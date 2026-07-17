<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\FacilityBooking;
use App\Models\User;

class FacilityBookingPolicy
{
    public function view(User $user, FacilityBooking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $booking->resident_id;
    }

    public function cancel(User $user, FacilityBooking $booking): bool
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved])) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $booking->resident_id;
    }
}
