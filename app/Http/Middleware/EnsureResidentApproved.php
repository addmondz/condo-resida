<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResidentApproved
{
    /**
     * Block unapproved resident accounts from protected resident functions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== UserStatus::Approved) {
            return response()->json([
                'success' => false,
                'message' => 'Your account must be approved before you can access this feature.',
                'data' => null,
                'errors' => [
                    'account_status' => [$user?->status?->value ?? 'unauthenticated'],
                ],
            ], 403);
        }

        return $next($request);
    }
}
