<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'resident_type' => $this->resident_type?->value,
            'avatar' => $this->avatar,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'unit' => $this->when(
                $this->relationLoaded('primaryUnit') && $this->primaryUnit,
                function () {
                    $assignment = $this->primaryUnit;

                    return [
                        'uuid' => $assignment->unit?->uuid,
                        'name' => $assignment->unit?->name,
                        'block_name' => $assignment->unit?->block?->name,
                        'property_name' => $assignment->unit?->block?->property?->name,
                    ];
                }
            ),
            'has_unit_assignment' => $this->unitAssignments()->exists(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
