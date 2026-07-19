<?php

namespace App\Services;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\AppNotification;
use App\Models\NotificationRecipient;

class NotificationService
{
    public function notifyUser(int $userId, NotificationType $type, string $title, string $body): AppNotification
    {
        $notification = AppNotification::create([
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'target_type' => NotificationTargetType::Residents,
            'status' => 'published',
            'published_at' => now(),
        ]);

        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id' => $userId,
        ]);

        return $notification;
    }

    public function notifyVisitorCheckedIn(int $residentId, string $visitorName): void
    {
        $this->notifyUser(
            $residentId,
            NotificationType::VisitorCheckedIn,
            'Visitor Checked In',
            "Your visitor {$visitorName} has checked in at the guard house."
        );
    }

    public function notifyVisitorCheckedOut(int $residentId, string $visitorName): void
    {
        $this->notifyUser(
            $residentId,
            NotificationType::VisitorCheckedOut,
            'Visitor Checked Out',
            "Your visitor {$visitorName} has checked out."
        );
    }

    public function notifyBookingApproved(int $residentId, string $facilityName, string $date): void
    {
        $this->notifyUser(
            $residentId,
            NotificationType::BookingApproved,
            'Booking Approved',
            "Your booking for {$facilityName} on {$date} has been approved."
        );
    }

    public function notifyBookingRejected(int $residentId, string $facilityName, string $date, string $reason): void
    {
        $this->notifyUser(
            $residentId,
            NotificationType::BookingRejected,
            'Booking Rejected',
            "Your booking for {$facilityName} on {$date} has been rejected. Reason: {$reason}"
        );
    }

    public function notifyRegistrationApproved(int $userId): void
    {
        $this->notifyUser(
            $userId,
            NotificationType::RegistrationApproved,
            'Registration Approved',
            'Your account has been approved. You can now access all resident features.'
        );
    }

    public function notifyRegistrationRejected(int $userId, string $reason): void
    {
        $this->notifyUser(
            $userId,
            NotificationType::RegistrationRejected,
            'Registration Rejected',
            "Your account registration has been rejected. Reason: {$reason}"
        );
    }
}
