<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\VisitorRegistrationResource;
use App\Services\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected VisitorService $visitorService
    ) {}

    /**
     * Validate a QR token and return visitor details.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
        ]);

        $result = $this->visitorService->scanQrToken($request->input('qr_token'), $request->user());

        return $this->success([
            'result' => $result['result'],
            'message' => $result['message'],
            'can_check_in' => $result['can_check_in'],
            'can_check_out' => $result['can_check_out'],
            'visitor' => $result['visitor'] ? new VisitorRegistrationResource($result['visitor']) : null,
        ], $result['message']);
    }
}
