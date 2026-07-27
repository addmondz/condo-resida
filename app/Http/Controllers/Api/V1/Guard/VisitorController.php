<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Resources\VisitorRegistrationResource;
use App\Models\VisitorRegistration;
use App\Services\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function __construct(
        protected VisitorService $visitorService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = VisitorRegistration::with(['resident', 'property', 'block', 'unit', 'checkedInBy', 'checkedOutBy']);

        $this->applyPropertyScope($query);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->input('date'));
        } else {
            $query->whereDate('visit_date', today());
        }

        $visitors = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return $this->success(VisitorRegistrationResource::collection($visitors)->response()->getData(true));
    }

    public function show(VisitorRegistration $visitor): JsonResponse
    {
        $visitor->load(['resident', 'property', 'block', 'unit', 'checkedInBy', 'checkedOutBy', 'activityLogs.guardUser']);

        return $this->success(new VisitorRegistrationResource($visitor));
    }

    public function checkIn(VisitorRegistration $visitor, Request $request): JsonResponse
    {
        $notes = $request->input('notes');
        $visitor = $this->visitorService->checkIn($visitor, auth()->user(), $notes);

        return $this->success(
            new VisitorRegistrationResource($visitor),
            'Visitor checked in successfully.'
        );
    }

    public function checkOut(VisitorRegistration $visitor, Request $request): JsonResponse
    {
        $notes = $request->input('notes');
        $visitor = $this->visitorService->checkOut($visitor, auth()->user(), $notes);

        return $this->success(
            new VisitorRegistrationResource($visitor),
            'Visitor checked out successfully.'
        );
    }
}
