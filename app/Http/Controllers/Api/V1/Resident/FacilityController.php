<?php

namespace App\Http\Controllers\Api\V1\Resident;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Services\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected FacilityService $facilityService
    ) {}

    /**
     * List active facilities.
     */
    public function index(): JsonResponse
    {
        $facilities = Facility::with('property')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->success(FacilityResource::collection($facilities));
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
     * Get available slots for a facility on a given date.
     */
    public function availability(Facility $facility, Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $slots = $this->facilityService->getAvailableSlots($facility, $request->input('date'));

        return $this->success($slots);
    }
}
