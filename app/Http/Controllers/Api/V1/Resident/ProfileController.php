<?php

namespace App\Http\Controllers\Api\V1\Resident;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Show the authenticated resident's profile.
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user));
    }

    /**
     * Update the authenticated resident's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'Profile updated successfully.');
    }
}
