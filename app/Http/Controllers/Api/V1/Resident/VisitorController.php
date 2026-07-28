<?php

namespace App\Http\Controllers\Api\V1\Resident;

use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreVisitorRequest;
use App\Http\Resources\VisitorRegistrationResource;
use App\Models\VisitorRegistration;
use App\Services\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected VisitorService $visitorService
    ) {}

    /**
     * List the resident's visitor registrations with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = VisitorRegistration::where('resident_id', auth()->id())
            ->with(['property', 'block', 'unit', 'checkedInBy', 'checkedOutBy']);

        if ($request->input('filter') === 'upcoming') {
            $query->whereIn('status', [VisitorStatus::Active->value, VisitorStatus::CheckedIn->value]);
        } elseif ($request->input('filter') === 'history') {
            $query->whereNotIn('status', [VisitorStatus::Active->value, VisitorStatus::CheckedIn->value]);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->input('date'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('visit_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('visit_date', '<=', $request->input('to_date'));
        }

        $sort = $request->input('sort', 'visit_date');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['visitor_name', 'purpose', 'visit_date', 'status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'visit_date';
        }

        $visitors = $query->orderBy($sort, $direction)
            ->orderBy('created_at', $direction)
            ->paginate($request->integer('per_page', 10));

        return $this->success(VisitorRegistrationResource::collection($visitors)->response()->getData(true));
    }

    /**
     * Register a new visitor.
     */
    public function store(StoreVisitorRequest $request): JsonResponse
    {
        $result = $this->visitorService->createVisitor(auth()->user(), $request->validated());

        return $this->success([
            'visitor' => new VisitorRegistrationResource($result['visitor']),
            'qr_token' => $result['qr_token'],
        ], 'Visitor registered successfully.', 201);
    }

    /**
     * Show a specific visitor registration.
     */
    public function show(VisitorRegistration $visitor): JsonResponse
    {
        $this->authorize('view', $visitor);

        $visitor->load(['resident', 'property', 'block', 'unit', 'checkedInBy', 'checkedOutBy']);

        return $this->success(new VisitorRegistrationResource($visitor));
    }

    /**
     * Cancel a visitor registration.
     */
    public function cancel(VisitorRegistration $visitor, Request $request): JsonResponse
    {
        $this->authorize('cancel', $visitor);

        $reason = $request->input('reason');
        $visitor = $this->visitorService->cancelVisitor($visitor, auth()->user(), $reason);
        $visitor->load(['property', 'block', 'unit']);

        return $this->success(
            new VisitorRegistrationResource($visitor),
            'Visitor registration cancelled successfully.'
        );
    }

    /**
     * Get the QR code data for a visitor registration.
     */
    public function qr(VisitorRegistration $visitor): JsonResponse
    {
        $this->authorize('view', $visitor);

        if ($visitor->status !== VisitorStatus::Active || ! $visitor->encrypted_qr_token) {
            return $this->error('This visitor QR code is no longer available.', 422, [
                'visitor' => ['Only active visitor passes can display a QR code.'],
            ]);
        }

        return $this->success([
            'uuid' => $visitor->uuid,
            'reference_number' => $visitor->reference_number,
            'qr_token' => $visitor->encrypted_qr_token,
        ]);
    }
}
