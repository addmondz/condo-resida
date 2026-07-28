<?php

namespace App\Http\Controllers\Api\V1\Guard;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\VisitorRegistrationResource;
use App\Services\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $token = $request->input('qr_token');

        Log::info('[QR Scan] Guard #{guard} scanned token (length={len}), server_now={now}, server_today={today}', [
            'guard' => $request->user()->id,
            'len' => strlen($token),
            'now' => now()->toDateTimeString(),
            'today' => today()->toDateString(),
        ]);

        $result = $this->visitorService->scanQrToken($token, $request->user());

        Log::info('[QR Scan] Result: {result}, can_check_in={ci}, can_check_out={co}, visitor_date={vd}', [
            'result' => $result['result'],
            'ci' => $result['can_check_in'] ? 'yes' : 'no',
            'co' => $result['can_check_out'] ? 'yes' : 'no',
            'vd' => $result['visitor']?->visit_date?->toDateString() ?? 'n/a',
        ]);

        return $this->success([
            'result' => $result['result'],
            'message' => $result['message'],
            'can_check_in' => $result['can_check_in'],
            'can_check_out' => $result['can_check_out'],
            'visitor' => $result['visitor'] ? new VisitorRegistrationResource($result['visitor']) : null,
        ], $result['message']);
    }
}
