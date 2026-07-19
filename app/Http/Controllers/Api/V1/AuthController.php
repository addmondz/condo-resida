<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OnboardingRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\BlockResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\UnitResource;
use App\Http\Resources\UserResource;
use App\Models\Block;
use App\Models\Property;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful. Your account is pending approval.', 201);
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful.');
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout(auth()->user());

        return $this->success(message: 'Logged out successfully.');
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user));
    }

    /**
     * Send a password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated('email'));

        return $this->success(message: 'Password reset link sent to your email.');
    }

    /**
     * Reset the password using a valid token.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return $this->success(message: 'Password has been reset successfully.');
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            auth()->user(),
            $request->validated('current_password'),
            $request->validated('password')
        );

        return $this->success(message: 'Password changed successfully.');
    }

    /**
     * Complete user onboarding (property/unit assignment).
     */
    public function onboarding(OnboardingRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->unitAssignments()->exists()) {
            return $this->error('Onboarding has already been completed.', 409);
        }

        $user = $this->authService->completeOnboarding($user, $request->validated());

        return $this->success(new UserResource($user), 'Onboarding completed successfully.');
    }

    /**
     * List all properties (for registration dropdown).
     */
    public function properties(): JsonResponse
    {
        $properties = Property::orderBy('name')->get();

        return $this->success(PropertyResource::collection($properties));
    }

    /**
     * List blocks for a property (for registration dropdown).
     */
    public function blocks(Property $property): JsonResponse
    {
        $blocks = $property->blocks()->orderBy('name')->get();

        return $this->success(BlockResource::collection($blocks));
    }

    /**
     * List units for a block (for registration dropdown).
     */
    public function units(Block $block): JsonResponse
    {
        $units = $block->units()->orderBy('name')->get();

        return $this->success(UnitResource::collection($units));
    }
}
