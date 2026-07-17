<?php

namespace App\Http\Requests\Admin;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'type' => ['required', new Enum(NotificationType::class)],
            'target_type' => ['required', new Enum(NotificationTargetType::class)],
            'target_id' => ['nullable', 'integer'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ];
    }
}
