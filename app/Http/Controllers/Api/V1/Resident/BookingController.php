<?php

namespace App\Http\Controllers\Api\V1\Resident;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBookingRequest;
use App\Http\Resources\FacilityBookingResource;
use App\Models\FacilityBooking;
use App\Services\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected FacilityService $facilityService
    ) {}

    /**
     * List the resident's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = FacilityBooking::where('resident_id', auth()->id())
            ->with(['facility.property', 'approvedBy'])
            ->orderByDesc('booking_date')
            ->paginate($request->integer('per_page', 15));

        return $this->success(FacilityBookingResource::collection($bookings)->response()->getData(true));
    }

    /**
     * Create a new booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->facilityService->createBooking(auth()->user(), $request->validated());

        return $this->success(
            new FacilityBookingResource($booking),
            'Booking created successfully.',
            201
        );
    }

    /**
     * Cancel a booking.
     */
    public function cancel(FacilityBooking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $booking = $this->facilityService->cancelBooking($booking, auth()->user());

        return $this->success(
            new FacilityBookingResource($booking),
            'Booking cancelled successfully.'
        );
    }
}
