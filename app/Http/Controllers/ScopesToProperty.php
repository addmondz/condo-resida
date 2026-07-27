<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToProperty
{
    protected function scopedPropertyId(): ?int
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return null;
        }

        return $user->property_id;
    }

    protected function applyPropertyScope(Builder $query, string $column = 'property_id'): Builder
    {
        $propertyId = $this->scopedPropertyId();

        if ($propertyId !== null) {
            $query->where($column, $propertyId);
        }

        return $query;
    }

    protected function authorizePropertyAccess(Property $property): bool
    {
        $propertyId = $this->scopedPropertyId();

        return $propertyId === null || $propertyId === $property->id;
    }

    protected function isSuperAdmin(): bool
    {
        return auth()->user()->isSuperAdmin();
    }
}
