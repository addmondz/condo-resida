<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserStatus;
use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Models\FacilityBooking;
use App\Models\User;
use App\Models\VisitorRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function dashboard(): JsonResponse
    {
        $propertyId = $this->scopedPropertyId();

        $userQuery = User::query();
        if ($propertyId !== null) {
            $userQuery->where(function ($q) use ($propertyId) {
                $q->where('property_id', $propertyId)
                    ->orWhereHas('unitAssignments', fn ($sub) => $sub->where('property_id', $propertyId));
            });
        }

        $visitorQuery = fn () => $this->scopedVisitorQuery($propertyId);
        $bookingQuery = fn () => $this->scopedBookingQuery($propertyId);

        $stats = [
            'users' => [
                'total' => (clone $userQuery)->count(),
                'pending' => (clone $userQuery)->where('status', UserStatus::Pending)->count(),
                'approved' => (clone $userQuery)->where('status', UserStatus::Approved)->count(),
                'rejected' => (clone $userQuery)->where('status', UserStatus::Rejected)->count(),
                'suspended' => (clone $userQuery)->where('status', UserStatus::Suspended)->count(),
            ],
            'visitors' => [
                'today_total' => $visitorQuery()->whereDate('visit_date', today())->count(),
                'today_checked_in' => $visitorQuery()->whereDate('visit_date', today())
                    ->where('status', VisitorStatus::CheckedIn)->count(),
                'today_checked_out' => $visitorQuery()->whereDate('visit_date', today())
                    ->where('status', VisitorStatus::CheckedOut)->count(),
                'this_month' => $visitorQuery()->whereMonth('visit_date', now()->month)
                    ->whereYear('visit_date', now()->year)->count(),
            ],
            'bookings' => [
                'pending' => $bookingQuery()->where('status', BookingStatus::Pending)->count(),
                'approved_today' => $bookingQuery()->whereDate('booking_date', today())
                    ->where('status', BookingStatus::Approved)->count(),
                'this_month' => $bookingQuery()->whereMonth('booking_date', now()->month)
                    ->whereYear('booking_date', now()->year)->count(),
            ],
            'visitors_daily' => $this->dailyCounts($visitorQuery(), 'visit_date', 7),
            'bookings_daily' => $this->dailyCounts($bookingQuery(), 'booking_date', 7),
        ];

        return $this->success($stats);
    }

    private function scopedVisitorQuery(?int $propertyId): Builder
    {
        $query = VisitorRegistration::query();
        if ($propertyId !== null) {
            $query->where('property_id', $propertyId);
        }

        return $query;
    }

    private function scopedBookingQuery(?int $propertyId): Builder
    {
        $query = FacilityBooking::query();
        if ($propertyId !== null) {
            $query->whereHas('facility', fn ($q) => $q->where('property_id', $propertyId));
        }

        return $query;
    }

    private function dailyCounts(Builder $query, string $dateColumn, int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $counts = (clone $query)
            ->whereDate($dateColumn, '>=', $start)
            ->selectRaw("DATE({$dateColumn}) as date, COUNT(*) as count")
            ->groupByRaw("DATE({$dateColumn})")
            ->pluck('count', 'date')
            ->toArray();

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $result[] = [
                'date' => $date,
                'count' => $counts[$date] ?? 0,
            ];
        }

        return $result;
    }
}
