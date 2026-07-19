<?php

namespace App\Http\Requests\Admin;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'type' => ['required', new Enum(NotificationType::class)],
            'target_type' => ['required', new Enum(NotificationTargetType::class)],
            'target_id' => ['nullable', 'integer'],
            'property_uuid' => ['nullable', 'exists:properties,uuid'],
            'block_uuid' => ['nullable', 'exists:blocks,uuid'],
            'resident_uuids' => ['nullable', 'array'],
            'resident_uuids.*' => ['string', 'exists:users,uuid'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ];
    }
}
