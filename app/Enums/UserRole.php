<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case PropertyAdmin = 'property_admin';
    case Guard = 'guard';
    case Resident = 'resident';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::PropertyAdmin => 'Property Admin',
            self::Guard => 'Guard',
            self::Resident => 'Resident',
        };
    }
}
