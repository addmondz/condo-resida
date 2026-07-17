<?php

namespace App\Services;

use App\Enums\ResidentType;
use App\Enums\UserStatus;
use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     *
     * Creates user with pending status, assigns 'resident' role,
     * creates the UserUnitAssignment, and generates a Sanctum token.
     *
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $property = Property::where('uuid', $data['property_uuid'])->firstOrFail();
        $block = Block::where('uuid', $data['block_uuid'])->firstOrFail();
        $unit = Unit::where('uuid', $data['unit_uuid'])->firstOrFail();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'status' => UserStatus::Pending,
            'resident_type' => ResidentType::from($data['resident_type']),
        ]);

        $user->assignRole('resident');

        $user->unitAssignments()->create([
            'unit_id' => $unit->id,
            'property_id' => $property->id,
            'is_primary' => true,
            'assigned_at' => now(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['unitAssignments.unit.block.property', 'roles']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user.
     *
     * Validates credentials, checks user is not soft-deleted,
     * updates last_login_at, and returns the user with a token.
     *
     * @return array{user: User, token: string}
     *
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['unitAssignments.unit.block.property', 'roles']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the user by revoking the current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Send a password reset link to the given email.
     */
    public function forgotPassword(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Reset the user's password using a valid token.
     */
    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->update(['password' => $password]);
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $newPassword]);
    }
}
