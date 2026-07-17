<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorRegistration;

class VisitorRegistrationPolicy
{
    public function view(User $user, VisitorRegistration $visitor): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuard()) {
            return true;
        }

        return $user->id === $visitor->resident_id;
    }

    public function cancel(User $user, VisitorRegistration $visitor): bool
    {
        if (! $visitor->canCancel()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $visitor->resident_id && $visitor->isActive();
    }
}
