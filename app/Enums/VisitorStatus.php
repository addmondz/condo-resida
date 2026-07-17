<?php

namespace App\Enums;

enum VisitorStatus: string
{
    case Active = 'active';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
        };
    }
}
