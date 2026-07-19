<?php

namespace App\Http\Requests\Auth;

use App\Models\Block;
use App\Models\Unit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_uuid' => ['required', 'exists:properties,uuid'],
            'block_uuid' => ['required', 'exists:blocks,uuid'],
            'unit_uuid' => ['required', 'exists:units,uuid'],
            'resident_type' => ['required', 'in:owner,tenant'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $block = Block::where('uuid', $this->string('block_uuid'))->first();
                $unit = Unit::where('uuid', $this->string('unit_uuid'))->first();

                if (! $block || ! $unit) {
                    return;
                }

                if (! $block->property()->where('uuid', $this->string('property_uuid'))->exists()) {
                    $validator->errors()->add('block_uuid', 'The selected block does not belong to the selected property.');
                }

                if ($unit->block_id !== $block->id) {
                    $validator->errors()->add('unit_uuid', 'The selected unit does not belong to the selected block.');
                }
            },
        ];
    }
}
