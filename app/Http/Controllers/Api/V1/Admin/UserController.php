<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Requests\Admin\RejectUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Property;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function __construct(
        protected UserService $userService,
        protected AuthService $authService
    ) {}

    public function store(Request $request): JsonResponse
    {
        if (! $this->isSuperAdmin()) {
            return $this->unauthorized('Only super admins can create staff users.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:property_admin,guard'],
            'property_uuid' => ['required', 'exists:properties,uuid'],
        ]);

        $property = Property::where('uuid', $validated['property_uuid'])->firstOrFail();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'status' => UserStatus::Approved,
            'property_id' => $property->id,
        ]);

        $user->assignRole($validated['role']);
        $user->load(['roles', 'managedProperty', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'User created successfully.', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'primaryUnit.unit.block.property']);

        $propertyId = $this->scopedPropertyId();
        if ($propertyId !== null) {
            $query->where(function ($q) use ($propertyId) {
                $q->where('property_id', $propertyId)
                    ->orWhereHas('unitAssignments', fn ($sub) => $sub->where('property_id', $propertyId));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'email', 'status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $users = $query->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 10));

        return $this->success(UserResource::collection($users)->response()->getData(true));
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'primaryUnit.unit.block.property', 'approvals.performer']);

        return $this->success(new UserResource($user));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'resident_type' => ['sometimes', 'in:owner,tenant'],
        ]);

        $user->update($validated);
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'User updated successfully.');
    }

    public function approve(User $user): JsonResponse
    {
        $user = $this->userService->approveUser($user, auth()->user());
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'User approved successfully.');
    }

    public function reject(RejectUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->rejectUser($user, auth()->user(), $request->validated('reason'));
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'User rejected.');
    }

    public function suspend(Request $request, User $user): JsonResponse
    {
        $reason = $request->input('reason');
        $user = $this->userService->suspendUser($user, auth()->user(), $reason);
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'User suspended.');
    }

    public function reactivate(User $user): JsonResponse
    {
        $user = $this->userService->reactivateUser($user, auth()->user());
        $user->load(['roles', 'primaryUnit.unit.block.property']);

        return $this->success(new UserResource($user), 'User reactivated.');
    }

    public function resetPassword(User $user): JsonResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(message: 'Password reset link sent to user.');
        }

        return $this->error('Failed to send reset link.');
    }
}
