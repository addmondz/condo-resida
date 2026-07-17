<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all facilities.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Facility::with('property');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->input('property_id'));
        }

        $facilities = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return $this->success(FacilityResource::collection($facilities)->response()->getData(true));
    }

    /**
     * Show a specific facility.
     */
    public function show(Facility $facility): JsonResponse
    {
        $facility->load('property');

        return $this->success(new FacilityResource($facility));
    }

    /**
     * Create a new facility.
     */
    public function store(StoreFacilityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $property = Property::where('uuid', $data['property_uuid'])->firstOrFail();
        unset($data['property_uuid']);
        $data['property_id'] = $property->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('facilities', 'public');
        }

        $facility = Facility::create($data);
        $facility->load('property');

        return $this->success(new FacilityResource($facility), 'Facility created successfully.', 201);
    }

    /**
     * Update a facility.
     */
    public function update(Request $request, Facility $facility): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'rules' => ['sometimes', 'nullable', 'string'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'opening_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'closing_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'slot_duration' => ['sometimes', 'integer', 'min:15'],
            'max_bookings_per_resident' => ['sometimes', 'integer', 'min:1'],
            'advance_booking_days' => ['sometimes', 'integer', 'min:1'],
            'cancellation_hours' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_under_maintenance' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('facilities', 'public');
        }

        $facility->update($validated);
        $facility->load('property');

        return $this->success(new FacilityResource($facility), 'Facility updated successfully.');
    }
}
