<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
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
        $today = today();
        $propertyId = $this->scopedPropertyId();

        $base = fn () => $this->scopedQuery($propertyId);

        $stats = [
            'today_expected' => $base()->whereDate('visit_date', $today)
                ->where('status', VisitorStatus::Active)
                ->count(),
            'today_checked_in' => $base()->whereDate('visit_date', $today)
                ->where('status', VisitorStatus::CheckedIn)
                ->count(),
            'today_checked_out' => $base()->whereDate('visit_date', $today)
                ->where('status', VisitorStatus::CheckedOut)
                ->count(),
            'today_total' => $base()->whereDate('visit_date', $today)->count(),
            'visitors_daily' => $this->dailyCounts($propertyId, 7),
            'status_breakdown' => [
                'active' => $base()->whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::Active)->count(),
                'checked_in' => $base()->whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::CheckedIn)->count(),
                'checked_out' => $base()->whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::CheckedOut)->count(),
                'cancelled' => $base()->whereDate('visit_date', $today)
                    ->where('status', VisitorStatus::Cancelled)->count(),
            ],
        ];

        return $this->success($stats);
    }

    private function scopedQuery(?int $propertyId): Builder
    {
        $query = VisitorRegistration::query();
        if ($propertyId !== null) {
            $query->where('property_id', $propertyId);
        }

        return $query;
    }

    private function dailyCounts(?int $propertyId, int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $query = $this->scopedQuery($propertyId);
        $counts = $query->whereDate('visit_date', '>=', $start)
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
