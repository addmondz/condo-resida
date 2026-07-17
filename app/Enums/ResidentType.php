<?php

namespace App\Enums;

enum ResidentType: string
{
    case Owner = 'owner';
    case Tenant = 'tenant';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Tenant => 'Tenant',
        };
    }
}
