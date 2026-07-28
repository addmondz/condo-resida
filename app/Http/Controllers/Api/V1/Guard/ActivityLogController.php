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
        $query = VisitorActivityLog::with(['visitorRegistration.resident', 'guardUser']);

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

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['action', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $logs = $query->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 10));

        return $this->success($logs);
    }
}
