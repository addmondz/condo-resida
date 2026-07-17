<?php

namespace App\Enums;

enum ApprovalAction: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Reactivated = 'reactivated';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Suspended => 'Suspended',
            self::Reactivated => 'Reactivated',
        };
    }
}
