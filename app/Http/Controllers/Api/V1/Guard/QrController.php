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

        $visitor = $this->visitorService->validateQrToken($request->input('qr_token'));

        return $this->success(new VisitorRegistrationResource($visitor), 'QR code is valid.');
    }
}
