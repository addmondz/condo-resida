<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all system settings, optionally filtered by group.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SystemSetting::query();

        if ($request->filled('group')) {
            $query->where('group', $request->input('group'));
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        // Group by category
        $grouped = $settings->groupBy('group')->map(function ($items) {
            return $items->mapWithKeys(function ($item) {
                return [$item->key => $item->value];
            });
        });

        return $this->success($grouped);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.group' => ['required', 'string'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['required'],
            'settings.*.type' => ['sometimes', 'string', 'in:string,boolean,integer,float,json,array'],
        ]);

        foreach ($validated['settings'] as $setting) {
            SystemSetting::set(
                $setting['group'],
                $setting['key'],
                $setting['value'],
                $setting['type'] ?? 'string'
            );
        }

        return $this->success(message: 'Settings updated successfully.');
    }
}
