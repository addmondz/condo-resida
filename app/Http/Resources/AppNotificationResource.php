<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppNotificationResource extends JsonResource
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
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type?->value,
            'target_type' => $this->target_type?->value,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'creator' => $this->when(
                $this->relationLoaded('creator') && $this->creator,
                fn () => [
                    'uuid' => $this->creator->uuid,
                    'name' => $this->creator->name,
                ]
            ),
            'property' => $this->when(
                $this->relationLoaded('property') && $this->property,
                fn () => [
                    'uuid' => $this->property->uuid,
                    'name' => $this->property->name,
                ]
            ),
            'recipients_count' => $this->when(
                $this->relationLoaded('recipients'),
                fn () => $this->recipients->count()
            ),
            'read_at' => $this->when(
                $this->relationLoaded('recipients') || isset($this->pivot),
                fn () => $this->pivot?->read_at?->toIso8601String()
                    ?? $this->recipients?->first()?->read_at?->toIso8601String()
            ),
        ];
    }
}
