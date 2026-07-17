<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\VisitorRegistration;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get guard dashboard statistics.
     */
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
        ];

        return $this->success($stats);
    }
}
