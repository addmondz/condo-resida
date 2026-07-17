<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\VisitorActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponseTrait;

    /**
     * List visitor activity logs (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = VisitorActivityLog::with(['visitorRegistration.resident', 'guardUser'])
            ->orderByDesc('created_at');

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
