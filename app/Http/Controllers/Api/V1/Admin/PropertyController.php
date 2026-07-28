<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function index(Request $request): JsonResponse
    {
        $query = Property::withCount(['blocks', 'units']);

        $this->applyPropertyScope($query, 'id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'address', 'status', 'blocks_count', 'units_count'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $properties = $query
            ->orderBy($sort, $direction)
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10));

        return $this->success(PropertyResource::collection($properties)->response()->getData(true));
    }

    public function show(Property $property): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        $property->loadCount(['blocks', 'units']);

        return $this->success(new PropertyResource($property));
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->isSuperAdmin()) {
            return $this->unauthorized('Only super admins can create properties.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['status'] = 'active';

        $property = Property::create($validated);
        $property->loadCount(['blocks', 'units']);

        return $this->success(new PropertyResource($property), 'Property created successfully.', 201);
    }

    public function update(Request $request, Property $property): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $property->update($validated);
        $property->loadCount(['blocks', 'units']);

        return $this->success(new PropertyResource($property), 'Property updated successfully.');
    }

    public function destroy(Property $property): JsonResponse
    {
        if (! $this->isSuperAdmin()) {
            return $this->unauthorized('Only super admins can delete properties.');
        }

        $property->delete();

        return $this->success(message: 'Property deleted successfully.');
    }
}
