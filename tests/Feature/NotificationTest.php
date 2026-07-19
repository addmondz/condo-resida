<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\AppNotification;
use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserUnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $resident;

    private Property $property;

    private Block $block;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'resident', 'guard_name' => 'web']);
        Role::create(['name' => 'guard', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'property_admin', 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->property = Property::create(['name' => 'Broadleaf Residence']);
        $this->block = Block::create(['property_id' => $this->property->id, 'name' => '12A']);
        $unit = Unit::create(['block_id' => $this->block->id, 'name' => '184G']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '+60111111111',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $this->admin->assignRole('super_admin');

        $this->resident = User::create([
            'name' => 'Resident',
            'email' => 'resident@example.com',
            'phone' => '+60122222222',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $this->resident->assignRole('resident');

        UserUnitAssignment::create([
            'user_id' => $this->resident->id,
            'unit_id' => $unit->id,
            'property_id' => $this->property->id,
            'is_primary' => true,
            'assigned_at' => now(),
        ]);
    }

    public function test_admin_can_send_property_announcement_to_residents(): void
    {
        $token = $this->admin->createToken('auth')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/notifications', [
                'title' => 'Water Disruption',
                'body' => 'Water supply will be interrupted for maintenance.',
                'type' => 'announcement',
                'target_type' => 'property',
                'property_uuid' => $this->property->uuid,
                'status' => 'published',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('notification_recipients', [
            'user_id' => $this->resident->id,
        ]);
    }

    public function test_admin_can_save_notification_draft_without_dispatching(): void
    {
        $token = $this->admin->createToken('auth')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/notifications', [
                'title' => 'Draft Announcement',
                'body' => 'Draft body',
                'type' => 'announcement',
                'target_type' => 'all',
                'status' => 'draft',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseCount('notification_recipients', 0);
    }

    public function test_admin_can_archive_notification(): void
    {
        $token = $this->admin->createToken('auth')->plainTextToken;

        $notification = AppNotification::create([
            'title' => 'Old Notice',
            'body' => 'Body',
            'type' => 'announcement',
            'target_type' => 'all',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/admin/notifications/{$notification->uuid}/archive");

        $response->assertOk()
            ->assertJsonPath('data.status', 'archived');
    }
}
