<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const COUNT = 50;
    private const USER_MODEL = 'App\\Models\\User';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('properties')->where('name', 'like', 'Demo Property %')->exists()) {
            return;
        }

        $now = now();
        $roleIds = DB::table('roles')
            ->whereIn('name', ['property_admin', 'guard', 'resident'])
            ->pluck('id', 'name');

        $adminId = DB::table('users')
            ->where('email', 'admin@example.com')
            ->value('id') ?? DB::table('users')->value('id');

        $guardId = DB::table('users')
            ->where('email', 'guard@example.com')
            ->value('id') ?? $adminId;

        $propertyIds = [];
        $blockIds = [];
        $unitIds = [];
        $residentIds = [];
        $facilityIds = [];
        $visitorIds = [];
        $notificationIds = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $propertyIds[$i] = DB::table('properties')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => sprintf('Demo Property %02d', $i),
                'address' => sprintf('%d Demo Avenue, Kuala Lumpur', 100 + $i),
                'contact_email' => sprintf('demo.property.%02d@example.test', $i),
                'contact_phone' => sprintf('+6039001%04d', $i),
                'timezone' => 'Asia/Kuala_Lumpur',
                'status' => $i % 10 === 0 ? 'inactive' : 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $blockIds[$i] = DB::table('blocks')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'property_id' => $propertyIds[$i],
                'name' => sprintf('Block %02d', $i),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $unitIds[$i] = DB::table('units')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'block_id' => $blockIds[$i],
                'name' => sprintf('%02d-%02d', (($i - 1) % 20) + 1, $i),
                'floor' => (string) ((($i - 1) % 20) + 1),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $status = match ($i % 5) {
                1 => 'pending',
                2 => 'rejected',
                3 => 'suspended',
                default => 'approved',
            };

            $residentIds[$i] = DB::table('users')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => sprintf('Demo Resident %02d', $i),
                'email' => sprintf('demo.resident.%02d@example.test', $i),
                'phone' => sprintf('+6012300%04d', $i),
                'password' => Hash::make('password'),
                'status' => $status,
                'resident_type' => $i % 2 === 0 ? 'tenant' : 'owner',
                'property_id' => $propertyIds[$i],
                'email_verified_at' => $status === 'approved' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (isset($roleIds['resident'])) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $roleIds['resident'],
                    'model_type' => self::USER_MODEL,
                    'model_id' => $residentIds[$i],
                ]);
            }

            DB::table('user_unit_assignments')->insertOrIgnore([
                'user_id' => $residentIds[$i],
                'unit_id' => $unitIds[$i],
                'property_id' => $propertyIds[$i],
                'is_primary' => true,
                'assigned_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($status !== 'pending' && $adminId) {
                DB::table('user_approvals')->insert([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $residentIds[$i],
                    'action' => $status === 'rejected' ? 'rejected' : ($status === 'suspended' ? 'suspended' : 'approved'),
                    'previous_status' => 'pending',
                    'new_status' => $status,
                    'reason' => $status === 'rejected'
                        ? 'Demo rejection reason for testing.'
                        : 'Demo account status generated for testing.',
                    'performed_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $facilityIds[$i] = DB::table('facilities')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'property_id' => $propertyIds[$i],
                'name' => sprintf('Demo Facility %02d', $i),
                'description' => sprintf('Demo facility record %02d for list and booking testing.', $i),
                'rules' => 'Keep the area clean. Follow management instructions.',
                'capacity' => 10 + ($i % 40),
                'opening_time' => '08:00:00',
                'closing_time' => '22:00:00',
                'slot_duration' => $i % 3 === 0 ? 120 : 60,
                'max_bookings_per_resident' => 2,
                'advance_booking_days' => 30,
                'cancellation_hours' => 24,
                'is_active' => $i % 12 !== 0,
                'is_under_maintenance' => $i % 12 === 0,
                'status' => $i % 12 === 0 ? 'maintenance' : 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $visitorStatus = match ($i % 5) {
                1 => 'checked_in',
                2 => 'checked_out',
                3 => 'expired',
                4 => 'cancelled',
                default => 'active',
            };
            $visitDate = now()->addDays(($i % 20) - 5)->toDateString();
            $rawToken = 'demo-visitor-token-'.$i.'-'.Str::random(32);

            $visitorIds[$i] = DB::table('visitor_registrations')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'reference_number' => sprintf('DEMOVIS%05d', $i),
                'resident_id' => $residentIds[$i],
                'property_id' => $propertyIds[$i],
                'block_id' => $blockIds[$i],
                'unit_id' => $unitIds[$i],
                'purpose' => ['visitor', 'delivery', 'contractor', 'service_provider', 'family'][$i % 5],
                'visitor_name' => sprintf('Demo Visitor %02d', $i),
                'contact_number' => sprintf('+6017600%04d', $i),
                'vehicle_number' => $i % 3 === 0 ? sprintf('DEMO%04d', $i) : null,
                'visit_date' => $visitDate,
                'notes' => 'Demo visitor registration.',
                'entry_type' => 'single_entry',
                'qr_token_hash' => hash('sha256', $rawToken),
                'encrypted_qr_token' => Crypt::encryptString($rawToken),
                'status' => $visitorStatus,
                'checked_in_at' => in_array($visitorStatus, ['checked_in', 'checked_out'], true) ? now()->subHours(2) : null,
                'checked_in_by' => in_array($visitorStatus, ['checked_in', 'checked_out'], true) ? $guardId : null,
                'checked_out_at' => $visitorStatus === 'checked_out' ? now()->subHour() : null,
                'checked_out_by' => $visitorStatus === 'checked_out' ? $guardId : null,
                'cancelled_at' => $visitorStatus === 'cancelled' ? now()->subDay() : null,
                'cancelled_by' => $visitorStatus === 'cancelled' ? $residentIds[$i] : null,
                'cancellation_reason' => $visitorStatus === 'cancelled' ? 'Demo cancellation reason.' : null,
                'expired_at' => $visitorStatus === 'expired' ? now()->subDay() : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('facility_bookings')->insert([
                'uuid' => (string) Str::uuid(),
                'facility_id' => $facilityIds[$i],
                'resident_id' => $residentIds[$i],
                'booking_date' => now()->addDays($i)->toDateString(),
                'start_time' => sprintf('%02d:00:00', 8 + ($i % 10)),
                'end_time' => sprintf('%02d:00:00', 9 + ($i % 10)),
                'status' => ['pending', 'approved', 'rejected', 'cancelled', 'completed'][$i % 5],
                'rejection_reason' => $i % 5 === 2 ? 'Demo booking rejection reason.' : null,
                'approved_by' => in_array($i % 5, [1, 4], true) ? $adminId : null,
                'approved_at' => in_array($i % 5, [1, 4], true) ? now()->subDays(2) : null,
                'cancelled_at' => $i % 5 === 3 ? now()->subDay() : null,
                'cancelled_by' => $i % 5 === 3 ? $residentIds[$i] : null,
                'notes' => sprintf('Demo booking request %02d.', $i),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('visitor_activity_logs')->insert([
                'uuid' => (string) Str::uuid(),
                'visitor_registration_id' => $visitorIds[$i],
                'guard_id' => $guardId,
                'action' => ['qr_scanned', 'entry_approved', 'entry_rejected', 'checked_out'][$i % 4],
                'details' => json_encode(['source' => 'large_demo_dataset', 'record' => $i]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo Data Seeder',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $notificationIds[$i] = DB::table('notifications')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'property_id' => $propertyIds[$i],
                'title' => sprintf('Demo Notice %02d', $i),
                'body' => sprintf('This is demo notification %02d for testing notification lists and detail views.', $i),
                'type' => 'announcement',
                'target_type' => $i % 2 === 0 ? 'property' : 'all',
                'target_id' => $i % 2 === 0 ? $propertyIds[$i] : null,
                'status' => $i % 4 === 0 ? 'draft' : 'published',
                'published_at' => $i % 4 === 0 ? null : now()->subDays($i % 10),
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($notificationIds as $i => $notificationId) {
            DB::table('notification_recipients')->insertOrIgnore([
                'notification_id' => $notificationId,
                'user_id' => $residentIds[$i],
                'read_at' => $i % 3 === 0 ? now()->subHours($i) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $propertyIds = DB::table('properties')
            ->where('name', 'like', 'Demo Property %')
            ->pluck('id');

        $userIds = DB::table('users')
            ->where('email', 'like', 'demo.resident.%@example.test')
            ->pluck('id');

        $notificationIds = DB::table('notifications')
            ->where('title', 'like', 'Demo Notice %')
            ->pluck('id');

        DB::table('notification_recipients')->whereIn('notification_id', $notificationIds)->delete();
        DB::table('visitor_activity_logs')->where('user_agent', 'Demo Data Seeder')->delete();
        DB::table('facility_bookings')->whereIn('resident_id', $userIds)->delete();
        DB::table('visitor_registrations')->where('reference_number', 'like', 'DEMOVIS%')->delete();
        DB::table('notifications')->whereIn('id', $notificationIds)->delete();
        DB::table('user_approvals')->whereIn('user_id', $userIds)->delete();
        DB::table('user_unit_assignments')->whereIn('user_id', $userIds)->delete();
        DB::table('model_has_roles')->where('model_type', self::USER_MODEL)->whereIn('model_id', $userIds)->delete();
        DB::table('users')->whereIn('id', $userIds)->delete();
        DB::table('facilities')->whereIn('property_id', $propertyIds)->delete();
        DB::table('units')->whereIn('block_id', DB::table('blocks')->whereIn('property_id', $propertyIds)->pluck('id'))->delete();
        DB::table('blocks')->whereIn('property_id', $propertyIds)->delete();
        DB::table('properties')->whereIn('id', $propertyIds)->delete();
    }
};
