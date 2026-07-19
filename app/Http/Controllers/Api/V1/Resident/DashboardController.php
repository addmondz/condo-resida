<?php

namespace App\Http\Controllers\Api\V1\Resident;

use App\Enums\BookingStatus;
use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\FacilityBooking;
use App\Models\VisitorRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function dashboard(): JsonResponse
    {
        $userId = auth()->id();

        $stats = [
            'active_visitors' => VisitorRegistration::where('user_id', $userId)
                ->whereIn('status', [VisitorStatus::Active, VisitorStatus::CheckedIn])
                ->count(),
            'upcoming_bookings' => FacilityBooking::where('user_id', $userId)
                ->where('booking_date', '>=', today())
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->count(),
            'total_visitors' => VisitorRegistration::where('user_id', $userId)->count(),
            'visitors_daily' => $this->dailyCounts(
                VisitorRegistration::where('user_id', $userId),
                'visit_date',
                7
            ),
            'bookings_daily' => $this->dailyCounts(
                FacilityBooking::where('user_id', $userId),
                'booking_date',
                7
            ),
            'visitor_status' => [
                'active' => VisitorRegistration::where('user_id', $userId)
                    ->where('status', VisitorStatus::Active)->count(),
                'checked_in' => VisitorRegistration::where('user_id', $userId)
                    ->where('status', VisitorStatus::CheckedIn)->count(),
                'checked_out' => VisitorRegistration::where('user_id', $userId)
                    ->where('status', VisitorStatus::CheckedOut)->count(),
                'expired' => VisitorRegistration::where('user_id', $userId)
                    ->where('status', VisitorStatus::Expired)->count(),
                'cancelled' => VisitorRegistration::where('user_id', $userId)
                    ->where('status', VisitorStatus::Cancelled)->count(),
            ],
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
