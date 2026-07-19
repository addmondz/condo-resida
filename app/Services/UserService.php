<?php

namespace App\Services;

use App\Enums\ApprovalAction;
use App\Enums\UserStatus;
use App\Models\User;

class UserService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Approve a pending user registration.
     */
    public function approveUser(User $user, User $admin): User
    {
        $previousStatus = $user->status;

        $user->update(['status' => UserStatus::Approved]);

        $user->approvals()->create([
            'action' => ApprovalAction::Approved,
            'previous_status' => $previousStatus->value,
            'new_status' => UserStatus::Approved->value,
            'performed_by' => $admin->id,
        ]);

        $this->notificationService->notifyRegistrationApproved($user->id);

        return $user->fresh();
    }

    /**
     * Reject a pending user registration.
     */
    public function rejectUser(User $user, User $admin, string $reason): User
    {
        $previousStatus = $user->status;

        $user->update(['status' => UserStatus::Rejected]);

        $user->approvals()->create([
            'action' => ApprovalAction::Rejected,
            'previous_status' => $previousStatus->value,
            'new_status' => UserStatus::Rejected->value,
            'reason' => $reason,
            'performed_by' => $admin->id,
        ]);

        $this->notificationService->notifyRegistrationRejected($user->id, $reason);

        return $user->fresh();
    }

    /**
     * Suspend an approved user.
     */
    public function suspendUser(User $user, User $admin, ?string $reason = null): User
    {
        $previousStatus = $user->status;

        $user->update(['status' => UserStatus::Suspended]);

        $user->approvals()->create([
            'action' => ApprovalAction::Suspended,
            'previous_status' => $previousStatus->value,
            'new_status' => UserStatus::Suspended->value,
            'reason' => $reason,
            'performed_by' => $admin->id,
        ]);

        return $user->fresh();
    }

    /**
     * Reactivate a suspended user.
     */
    public function reactivateUser(User $user, User $admin): User
    {
        $previousStatus = $user->status;

        $user->update(['status' => UserStatus::Approved]);

        $user->approvals()->create([
            'action' => ApprovalAction::Reactivated,
            'previous_status' => $previousStatus->value,
            'new_status' => UserStatus::Approved->value,
            'performed_by' => $admin->id,
        ]);

        return $user->fresh();
    }
}
