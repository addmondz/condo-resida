<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserStatus;
use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\FacilityBooking;
use App\Models\User;
use App\Models\VisitorRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function dashboard(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'pending' => User::where('status', UserStatus::Pending)->count(),
                'approved' => User::where('status', UserStatus::Approved)->count(),
                'rejected' => User::where('status', UserStatus::Rejected)->count(),
                'suspended' => User::where('status', UserStatus::Suspended)->count(),
            ],
            'visitors' => [
                'today_total' => VisitorRegistration::whereDate('visit_date', today())->count(),
                'today_checked_in' => VisitorRegistration::whereDate('visit_date', today())
                    ->where('status', VisitorStatus::CheckedIn)->count(),
                'today_checked_out' => VisitorRegistration::whereDate('visit_date', today())
                    ->where('status', VisitorStatus::CheckedOut)->count(),
                'this_month' => VisitorRegistration::whereMonth('visit_date', now()->month)
                    ->whereYear('visit_date', now()->year)->count(),
            ],
            'bookings' => [
                'pending' => FacilityBooking::where('status', BookingStatus::Pending)->count(),
                'approved_today' => FacilityBooking::whereDate('booking_date', today())
                    ->where('status', BookingStatus::Approved)->count(),
                'this_month' => FacilityBooking::whereMonth('booking_date', now()->month)
                    ->whereYear('booking_date', now()->year)->count(),
            ],
            'visitors_daily' => $this->dailyCounts(VisitorRegistration::query(), 'visit_date', 7),
            'bookings_daily' => $this->dailyCounts(FacilityBooking::query(), 'booking_date', 7),
        ];

        return $this->success($stats);
    }

    private function dailyCounts($query, string $dateColumn, int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $counts = $query
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
