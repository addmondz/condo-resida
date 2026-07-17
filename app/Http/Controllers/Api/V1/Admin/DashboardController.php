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

class DashboardController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get admin dashboard statistics.
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'pending' => User::where('status', UserStatus::Pending)->count(),
                'approved' => User::where('status', UserStatus::Approved)->count(),
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
        ];

        return $this->success($stats);
    }
}
