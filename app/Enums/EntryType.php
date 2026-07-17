<?php

namespace App\Enums;

enum EntryType: string
{
    case SingleEntry = 'single_entry';

    public function label(): string
    {
        return match ($this) {
            self::SingleEntry => 'Single Entry',
        };
    }
}
