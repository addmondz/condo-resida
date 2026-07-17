<?php

namespace App\Enums;

enum VisitorPurpose: string
{
    case Visitor = 'visitor';
    case Delivery = 'delivery';
    case Contractor = 'contractor';
    case ServiceProvider = 'service_provider';
    case Family = 'family';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Visitor => 'Visitor',
            self::Delivery => 'Delivery',
            self::Contractor => 'Contractor',
            self::ServiceProvider => 'Service Provider',
            self::Family => 'Family',
            self::Other => 'Other',
        };
    }
}
