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
    public function index(Request $request): JsonResponse
    {
        $query = Facility::with('property')
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'available') {
            $query->where('is_under_maintenance', false);
        } elseif ($request->input('status') === 'maintenance') {
            $query->where('is_under_maintenance', true);
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'capacity', 'opening_time', 'status'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $sortColumn = $sort === 'status' ? 'is_under_maintenance' : $sort;

        $facilities = $query->orderBy($sortColumn, $direction)
            ->paginate($request->integer('per_page', 10));

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
