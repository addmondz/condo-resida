<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
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
            'description' => $this->description,
            'rules' => $this->rules,
            'capacity' => $this->capacity,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'slot_duration' => $this->slot_duration,
            'max_bookings_per_resident' => $this->max_bookings_per_resident,
            'advance_booking_days' => $this->advance_booking_days,
            'cancellation_hours' => $this->cancellation_hours,
            'is_active' => $this->is_active,
            'is_under_maintenance' => $this->is_under_maintenance,
            'property' => new PropertyResource($this->whenLoaded('property')),
        ];
    }
}
