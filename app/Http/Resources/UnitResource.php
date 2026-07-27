<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
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
            'floor' => $this->floor,
            'status' => $this->status,
            'block' => new BlockResource($this->whenLoaded('block')),
            'residents' => $this->when(
                $this->relationLoaded('residents'),
                fn () => $this->residents->map(fn ($user) => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'resident_type' => $user->resident_type?->value,
                ])
            ),
        ];
    }
}
