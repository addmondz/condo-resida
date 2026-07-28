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
            ->with(['facility.property', 'approvedBy']);

        if ($request->input('filter') === 'upcoming') {
            $bookings->whereDate('booking_date', '>=', now()->toDateString());
        }

        if ($request->input('filter') === 'past') {
            $bookings->whereDate('booking_date', '<', now()->toDateString());
        }

        $sort = $request->input('sort', 'booking_date');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['booking_date', 'start_time', 'status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'booking_date';
        }

        $bookings = $bookings->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 10));

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
     * Show a single booking.
     */
    public function show(FacilityBooking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['facility.property', 'approvedBy']);

        return $this->success(new FacilityBookingResource($booking));
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
