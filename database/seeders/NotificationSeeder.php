<?php

namespace Database\Seeders;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\AppNotification;
use App\Models\NotificationRecipient;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $property = Property::where('name', 'Broadleaf Residence')->firstOrFail();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        // 1. Published announcement
        $welcomeNotification = AppNotification::create([
            'property_id' => $property->id,
            'title' => 'Welcome to Broadleaf Residence',
            'body' => 'Welcome to the Broadleaf Residence community portal. Here you can manage visitor registrations, book facilities, and stay updated with announcements from management.',
            'type' => NotificationType::Announcement,
            'target_type' => NotificationTargetType::All,
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        // Create recipient records for each user
        $users = User::all();
        foreach ($users as $user) {
            NotificationRecipient::create([
                'notification_id' => $welcomeNotification->id,
                'user_id' => $user->id,
                'read_at' => null,
            ]);
        }

        // 2. Draft announcement
        AppNotification::create([
            'property_id' => $property->id,
            'title' => 'Upcoming Maintenance',
            'body' => 'Please be informed that there will be scheduled maintenance for the water supply system on the coming weekend. Water supply will be temporarily interrupted from 10:00 AM to 4:00 PM.',
            'type' => NotificationType::Announcement,
            'target_type' => NotificationTargetType::All,
            'status' => 'draft',
            'published_at' => null,
            'created_by' => $admin->id,
        ]);
    }
}
