<?php

namespace App\Enums;

enum NotificationTargetType: string
{
    case All = 'all';
    case Property = 'property';
    case Block = 'block';
    case Residents = 'residents';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Property => 'Property',
            self::Block => 'Block',
            self::Residents => 'Residents',
        };
    }
}
