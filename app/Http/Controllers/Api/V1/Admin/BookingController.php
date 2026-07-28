<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Requests\Admin\RejectBookingRequest;
use App\Http\Resources\FacilityBookingResource;
use App\Models\FacilityBooking;
use App\Services\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function __construct(
        protected FacilityService $facilityService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = FacilityBooking::with(['facility.property', 'resident', 'approvedBy']);

        $propertyId = $this->scopedPropertyId();
        if ($propertyId !== null) {
            $query->whereHas('facility', fn ($q) => $q->where('property_id', $propertyId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->input('facility_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->input('date'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('booking_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('booking_date', '<=', $request->input('to_date'));
        }

        $sort = $request->input('sort', 'booking_date');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['booking_date', 'start_time', 'status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'booking_date';
        }

        $bookings = $query->orderBy($sort, $direction)
            ->orderBy('created_at', $direction)
            ->paginate($request->integer('per_page', 10));

        return $this->success(FacilityBookingResource::collection($bookings)->response()->getData(true));
    }

    public function show(FacilityBooking $booking): JsonResponse
    {
        $booking->load(['facility.property', 'resident', 'approvedBy', 'cancelledBy']);

        return $this->success(new FacilityBookingResource($booking));
    }

    public function approve(FacilityBooking $booking): JsonResponse
    {
        $booking = $this->facilityService->approveBooking($booking, auth()->user());

        return $this->success(
            new FacilityBookingResource($booking),
            'Booking approved successfully.'
        );
    }

    public function reject(RejectBookingRequest $request, FacilityBooking $booking): JsonResponse
    {
        $booking = $this->facilityService->rejectBooking(
            $booking,
            auth()->user(),
            $request->validated('reason')
        );

        return $this->success(
            new FacilityBookingResource($booking),
            'Booking rejected.'
        );
    }

    public function cancel(FacilityBooking $booking): JsonResponse
    {
        $booking = $this->facilityService->cancelBooking($booking, auth()->user());

        return $this->success(
            new FacilityBookingResource($booking),
            'Booking cancelled.'
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $query = FacilityBooking::with(['facility', 'resident']);

        $propertyId = $this->scopedPropertyId();
        if ($propertyId !== null) {
            $query->whereHas('facility', fn ($q) => $q->where('property_id', $propertyId));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('booking_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('booking_date', '<=', $request->input('to_date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->orderByDesc('booking_date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bookings_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($bookings) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Facility',
                'Resident',
                'Booking Date',
                'Start Time',
                'End Time',
                'Status',
                'Rejection Reason',
                'Notes',
                'Created At',
            ]);

            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    $booking->facility?->name,
                    $booking->resident?->name,
                    $booking->booking_date?->format('Y-m-d'),
                    $booking->start_time,
                    $booking->end_time,
                    $booking->status?->label(),
                    $booking->rejection_reason,
                    $booking->notes,
                    $booking->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
