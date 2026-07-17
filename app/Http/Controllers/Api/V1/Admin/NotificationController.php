<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\NotificationTargetType;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AppNotification::with('creator')->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $notifications = $query->paginate($request->integer('per_page', 15));

        return $this->success(AppNotificationResource::collection($notifications)->response()->getData(true));
    }

    /**
     * Create a new notification and dispatch it to recipients.
     */
    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $notification = AppNotification::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'target_type' => $data['target_type'],
            'target_id' => $data['target_id'] ?? null,
            'property_id' => $data['property_id'] ?? null,
            'status' => 'published',
            'published_at' => now(),
            'created_by' => auth()->id(),
        ]);

        // Determine recipients based on target type
        $targetType = NotificationTargetType::from($data['target_type']);
        $recipientQuery = User::query();

        switch ($targetType) {
            case NotificationTargetType::All:
                $recipientQuery->whereHas('roles', function ($q) {
                    $q->where('name', 'resident');
                });
                break;

            case NotificationTargetType::Property:
                if (isset($data['target_id'])) {
                    $recipientQuery->whereHas('unitAssignments', function ($q) use ($data) {
                        $q->where('property_id', $data['target_id']);
                    });
                }
                break;

            case NotificationTargetType::Block:
                if (isset($data['target_id'])) {
                    $recipientQuery->whereHas('unitAssignments.unit', function ($q) use ($data) {
                        $q->where('block_id', $data['target_id']);
                    });
                }
                break;

            case NotificationTargetType::Residents:
                // target_id is ignored; all residents get it
                $recipientQuery->whereHas('roles', function ($q) {
                    $q->where('name', 'resident');
                });
                break;
        }

        $recipientIds = $recipientQuery->pluck('id');

        $recipients = $recipientIds->map(fn ($userId) => [
            'notification_id' => $notification->id,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationRecipient::insert($recipients->toArray());

        return $this->success(
            new AppNotificationResource($notification),
            'Notification created and dispatched.',
            201
        );
    }
}
