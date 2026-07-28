<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\NotificationTargetType;
use App\Enums\UserStatus;
use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use App\Models\Block;
use App\Models\NotificationRecipient;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function index(Request $request): JsonResponse
    {
        $query = AppNotification::with('creator');

        $this->applyPropertyScope($query);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['title', 'type', 'target_type', 'status', 'published_at', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $notifications = $query->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 10));

        return $this->success(AppNotificationResource::collection($notifications)->response()->getData(true));
    }

    public function show(AppNotification $notification): JsonResponse
    {
        $notification->load(['creator', 'property', 'recipients.user']);

        return $this->success(new AppNotificationResource($notification));
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $target = $this->resolveTarget($data);
        $status = $data['status'] ?? 'published';

        if (! $this->isSuperAdmin() && $target['property_id'] !== null) {
            $propertyId = $this->scopedPropertyId();
            if ($propertyId !== null && $target['property_id'] !== $propertyId) {
                return $this->unauthorized('You can only create notifications for your property.');
            }
        }

        $notification = DB::transaction(function () use ($data, $target, $status): AppNotification {
            $notification = AppNotification::create([
                'title' => $data['title'],
                'body' => $data['body'],
                'type' => $data['type'],
                'target_type' => $data['target_type'],
                'target_id' => $target['target_id'],
                'property_id' => $target['property_id'],
                'status' => $status,
                'published_at' => $status === 'published' ? now() : null,
                'created_by' => auth()->id(),
            ]);

            if ($status === 'published') {
                $this->dispatchNotification($notification, $data, $target);
            }

            return $notification;
        });

        return $this->success(
            new AppNotificationResource($notification),
            $status === 'published' ? 'Notification created and dispatched.' : 'Notification saved as draft.',
            201
        );
    }

    public function publish(AppNotification $notification): JsonResponse
    {
        if ($notification->status === 'archived') {
            return $this->error('Archived notifications cannot be published.', 422);
        }

        $notification->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->dispatchNotification($notification, [
            'target_type' => $notification->target_type->value,
        ], [
            'target_id' => $notification->target_id,
            'property_id' => $notification->property_id,
            'resident_ids' => null,
        ]);

        return $this->success(new AppNotificationResource($notification->fresh()), 'Notification published.');
    }

    public function archive(AppNotification $notification): JsonResponse
    {
        $notification->update(['status' => 'archived']);

        return $this->success(new AppNotificationResource($notification->fresh()), 'Notification archived.');
    }

    private function resolveTarget(array $data): array
    {
        $targetType = NotificationTargetType::from($data['target_type']);

        return match ($targetType) {
            NotificationTargetType::Property => $this->resolvePropertyTarget($data),
            NotificationTargetType::Block => $this->resolveBlockTarget($data),
            NotificationTargetType::SelectedResidents => [
                'target_id' => null,
                'property_id' => null,
                'resident_ids' => User::whereIn('uuid', $data['resident_uuids'] ?? [])->pluck('id')->all(),
            ],
            default => [
                'target_id' => null,
                'property_id' => null,
                'resident_ids' => null,
            ],
        };
    }

    private function resolvePropertyTarget(array $data): array
    {
        $property = isset($data['property_uuid'])
            ? Property::where('uuid', $data['property_uuid'])->first()
            : Property::find($data['target_id'] ?? null);

        return [
            'target_id' => $property?->id,
            'property_id' => $property?->id,
            'resident_ids' => null,
        ];
    }

    private function resolveBlockTarget(array $data): array
    {
        $block = isset($data['block_uuid'])
            ? Block::where('uuid', $data['block_uuid'])->first()
            : Block::find($data['target_id'] ?? null);

        return [
            'target_id' => $block?->id,
            'property_id' => $block?->property_id,
            'resident_ids' => null,
        ];
    }

    private function dispatchNotification(AppNotification $notification, array $data, array $target): void
    {
        $recipientQuery = User::query()
            ->where('status', UserStatus::Approved)
            ->whereHas('roles', fn ($q) => $q->where('name', 'resident'));

        $targetType = NotificationTargetType::from($data['target_type']);

        match ($targetType) {
            NotificationTargetType::Property => $recipientQuery->whereHas('unitAssignments', fn ($q) => $q->where('property_id', $target['target_id'])),
            NotificationTargetType::Block => $recipientQuery->whereHas('unitAssignments.unit', fn ($q) => $q->where('block_id', $target['target_id'])),
            NotificationTargetType::SelectedResidents => $recipientQuery->whereIn('id', $target['resident_ids'] ?? []),
            default => null,
        };

        $recipientQuery->pluck('id')->each(function (int $userId) use ($notification): void {
            NotificationRecipient::firstOrCreate([
                'notification_id' => $notification->id,
                'user_id' => $userId,
            ]);
        });
    }
}
