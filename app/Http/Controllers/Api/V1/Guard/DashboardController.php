<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\VisitorRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function dashboard(): JsonResponse
    {
        $today = today();

        $stats = [
            'today_expected' => VisitorRegistration::whereDate('visit_date', $today)
                ->where('status', VisitorStatus::Active)
                ->count(),
            'today_checked_in' => VisitorRegistration::whereDate('visit_date', $today)
                ->where('status', VisitorStatus::CheckedIn)
                ->count(),
            'today_checked_out' => VisitorRegistration::whereDate('visit_date', $today)
                ->where('status', VisitorStatus::CheckedOut)
                ->count(),
            'today_total' => VisitorRegistration::whereDate('visit_date', $today)->count(),
            'visitors_daily' => $this->dailyCounts(7),
            'status_breakdown' => [
                'active' => VisitorRegistration::whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::Active)->count(),
                'checked_in' => VisitorRegistration::whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::CheckedIn)->count(),
                'checked_out' => VisitorRegistration::whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::CheckedOut)->count(),
                'cancelled' => VisitorRegistration::whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::Cancelled)->count(),
            ],
        ];

        return $this->success($stats);
    }

    private function dailyCounts(int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $counts = VisitorRegistration::whereDate('visit_date', '>=', $start)
            ->selectRaw('DATE(visit_date) as date, COUNT(*) as count')
            ->groupByRaw('DATE(visit_date)')
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
