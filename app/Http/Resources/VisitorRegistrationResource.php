<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorRegistrationResource extends JsonResource
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
            'reference_number' => $this->reference_number,
            'purpose' => $this->purpose?->label(),
            'visitor_name' => $this->visitor_name,
            'contact_number' => $this->contact_number,
            'vehicle_number' => $this->vehicle_number,
            'visit_date' => $this->visit_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'entry_type' => $this->entry_type?->value,
            'status' => $this->status?->label(),
            'resident' => new UserResource($this->whenLoaded('resident')),
            'property_name' => $this->whenLoaded('property', fn () => $this->property?->name),
            'block_name' => $this->whenLoaded('block', fn () => $this->block?->name),
            'unit_name' => $this->whenLoaded('unit', fn () => $this->unit?->name),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'checked_in_by' => $this->whenLoaded('checkedInBy', fn () => $this->checkedInBy?->name),
            'checked_out_at' => $this->checked_out_at?->toIso8601String(),
            'checked_out_by' => $this->whenLoaded('checkedOutBy', fn () => $this->checkedOutBy?->name),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
