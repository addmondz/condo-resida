<?php

namespace App\Enums;

enum NotificationType: string
{
    case Announcement = 'announcement';
    case VisitorCheckedIn = 'visitor_checked_in';
    case VisitorCheckedOut = 'visitor_checked_out';
    case VisitorCancelled = 'visitor_cancelled';
    case VisitorExpired = 'visitor_expired';
    case BookingApproved = 'booking_approved';
    case BookingRejected = 'booking_rejected';
    case BookingCancelled = 'booking_cancelled';
    case RegistrationApproved = 'registration_approved';
    case RegistrationRejected = 'registration_rejected';

    public function label(): string
    {
        return match ($this) {
            self::Announcement => 'Announcement',
            self::VisitorCheckedIn => 'Visitor Checked In',
            self::VisitorCheckedOut => 'Visitor Checked Out',
            self::VisitorCancelled => 'Visitor Cancelled',
            self::VisitorExpired => 'Visitor Expired',
            self::BookingApproved => 'Booking Approved',
            self::BookingRejected => 'Booking Rejected',
            self::BookingCancelled => 'Booking Cancelled',
            self::RegistrationApproved => 'Registration Approved',
            self::RegistrationRejected => 'Registration Rejected',
        };
    }
}
