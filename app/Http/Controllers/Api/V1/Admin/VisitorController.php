<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\VisitorStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Requests\Admin\CancelVisitorRequest;
use App\Http\Resources\VisitorRegistrationResource;
use App\Models\VisitorRegistration;
use App\Services\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->input('property_id'));
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

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
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

    public function show(VisitorRegistration $visitor): JsonResponse
    {
        $visitor->load([
            'resident',
            'property',
            'block',
            'unit',
            'checkedInBy',
            'checkedOutBy',
            'cancelledBy',
            'activityLogs.guardUser',
        ]);

        return $this->success(new VisitorRegistrationResource($visitor));
    }

    public function qr(VisitorRegistration $visitor): JsonResponse
    {
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

    public function cancel(CancelVisitorRequest $request, VisitorRegistration $visitor): JsonResponse
    {
        $visitor = $this->visitorService->cancelVisitor(
            $visitor,
            auth()->user(),
            $request->validated('reason')
        );

        $visitor->load(['property', 'block', 'unit']);

        return $this->success(
            new VisitorRegistrationResource($visitor),
            'Visitor registration cancelled.'
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $query = VisitorRegistration::with(['resident', 'property', 'block', 'unit']);

        $this->applyPropertyScope($query);

        if ($request->filled('from_date')) {
            $query->whereDate('visit_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('visit_date', '<=', $request->input('to_date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $visitors = $query->orderByDesc('visit_date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="visitors_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($visitors) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Reference',
                'Visitor Name',
                'Contact',
                'Vehicle',
                'Purpose',
                'Visit Date',
                'Status',
                'Resident',
                'Property',
                'Block',
                'Unit',
                'Checked In',
                'Checked Out',
                'Created At',
            ]);

            foreach ($visitors as $visitor) {
                fputcsv($handle, [
                    $visitor->reference_number,
                    $visitor->visitor_name,
                    $visitor->contact_number,
                    $visitor->vehicle_number,
                    $visitor->purpose?->label(),
                    $visitor->visit_date?->format('Y-m-d'),
                    $visitor->status?->label(),
                    $visitor->resident?->name,
                    $visitor->property?->name,
                    $visitor->block?->name,
                    $visitor->unit?->name,
                    $visitor->checked_in_at?->format('Y-m-d H:i:s'),
                    $visitor->checked_out_at?->format('Y-m-d H:i:s'),
                    $visitor->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
