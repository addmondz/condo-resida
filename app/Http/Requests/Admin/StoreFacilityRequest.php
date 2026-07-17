<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:2048'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i', 'after:opening_time'],
            'slot_duration' => ['nullable', 'integer', 'min:15'],
            'max_bookings_per_resident' => ['nullable', 'integer', 'min:1'],
            'advance_booking_days' => ['nullable', 'integer', 'min:1'],
            'cancellation_hours' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'property_uuid' => ['required', 'exists:properties,uuid'],
        ];
    }
}
