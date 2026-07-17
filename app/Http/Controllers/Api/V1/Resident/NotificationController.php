<?php

namespace App\Http\Controllers\Api\V1\Resident;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use App\Models\NotificationRecipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * List notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::whereHas('recipients', function ($query) {
            $query->where('user_id', auth()->id());
        })
            ->with(['recipients' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->orderByDesc('published_at')
            ->paginate($request->integer('per_page', 15));

        return $this->success(AppNotificationResource::collection($notifications)->response()->getData(true));
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(AppNotification $notification): JsonResponse
    {
        NotificationRecipient::where('notification_id', $notification->id)
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(message: 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        NotificationRecipient::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(message: 'All notifications marked as read.');
    }
}
