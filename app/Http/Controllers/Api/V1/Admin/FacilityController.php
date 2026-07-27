<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Requests\Admin\StoreFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Models\FacilityBlockedSlot;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function index(Request $request): JsonResponse
    {
        $query = Facility::with('property');

        $this->applyPropertyScope($query);

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->input('property_id'));
        }

        $facilities = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return $this->success(FacilityResource::collection($facilities)->response()->getData(true));
    }

    public function show(Facility $facility): JsonResponse
    {
        if (! $this->authorizePropertyAccess($facility->property)) {
            return $this->unauthorized('You do not have access to this facility.');
        }

        $facility->load('property');

        return $this->success(new FacilityResource($facility));
    }

    public function store(StoreFacilityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $property = Property::where('uuid', $data['property_uuid'])->firstOrFail();

        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        unset($data['property_uuid']);
        $data['property_id'] = $property->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('facilities', 'public');
        }

        $facility = Facility::create($data);
        $facility->load('property');

        return $this->success(new FacilityResource($facility), 'Facility created successfully.', 201);
    }

    public function update(Request $request, Facility $facility): JsonResponse
    {
        if (! $this->authorizePropertyAccess($facility->property)) {
            return $this->unauthorized('You do not have access to this facility.');
        }

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

    public function destroy(Facility $facility): JsonResponse
    {
        if (! $this->authorizePropertyAccess($facility->property)) {
            return $this->unauthorized('You do not have access to this facility.');
        }

        $facility->delete();

        return $this->success(message: 'Facility deleted successfully.');
    }

    public function blockedSlots(Facility $facility): JsonResponse
    {
        if (! $this->authorizePropertyAccess($facility->property)) {
            return $this->unauthorized('You do not have access to this facility.');
        }

        $slots = $facility->blockedSlots()
            ->orderByDesc('blocked_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (FacilityBlockedSlot $slot) => $this->blockedSlotPayload($slot));

        return $this->success($slots);
    }

    public function storeBlockedSlot(Request $request, Facility $facility): JsonResponse
    {
        if (! $this->authorizePropertyAccess($facility->property)) {
            return $this->unauthorized('You do not have access to this facility.');
        }

        $validated = $request->validate([
            'blocked_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'required_with:end_time', 'date_format:H:i'],
            'end_time' => ['nullable', 'required_with:start_time', 'date_format:H:i', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $slot = $facility->blockedSlots()->create($validated);

        return $this->success($this->blockedSlotPayload($slot), 'Facility slot blocked successfully.', 201);
    }

    public function destroyBlockedSlot(Facility $facility, FacilityBlockedSlot $blockedSlot): JsonResponse
    {
        if (! $this->authorizePropertyAccess($facility->property)) {
            return $this->unauthorized('You do not have access to this facility.');
        }

        if ($blockedSlot->facility_id !== $facility->id) {
            return $this->notFound('Blocked slot not found.');
        }

        $blockedSlot->delete();

        return $this->success(message: 'Blocked slot removed successfully.');
    }

    private function blockedSlotPayload(FacilityBlockedSlot $slot): array
    {
        return [
            'id' => $slot->id,
            'blocked_date' => $slot->blocked_date?->format('Y-m-d'),
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'reason' => $slot->reason,
        ];
    }
}
