<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Models\VisitorActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function index(Request $request): JsonResponse
    {
        $query = VisitorActivityLog::with(['visitorRegistration.resident', 'guardUser'])
            ->orderByDesc('created_at');

        $propertyId = $this->scopedPropertyId();
        if ($propertyId !== null) {
            $query->whereHas('visitorRegistration', fn ($q) => $q->where('property_id', $propertyId));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->paginate($request->integer('per_page', 15));

        return $this->success($logs);
    }
}
