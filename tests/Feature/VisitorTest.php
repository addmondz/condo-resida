<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserUnitAssignment;
use App\Models\VisitorActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VisitorTest extends TestCase
{
    use RefreshDatabase;

    private User $resident;

    private User $guard;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'resident', 'guard_name' => 'web']);
        Role::create(['name' => 'guard', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'property_admin', 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $property = Property::create(['name' => 'Test Property']);
        $block = Block::create(['property_id' => $property->id, 'name' => 'A']);
        $unit = Unit::create(['block_id' => $block->id, 'name' => '101', 'floor' => 1]);

        $this->resident = User::create([
            'name' => 'Resident',
            'email' => 'resident@example.com',
            'phone' => '+60123456789',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $this->resident->assignRole('resident');

        UserUnitAssignment::create([
            'user_id' => $this->resident->id,
            'unit_id' => $unit->id,
            'property_id' => $property->id,
            'is_primary' => true,
            'assigned_at' => now(),
        ]);

        $this->guard = User::create([
            'name' => 'Guard',
            'email' => 'guard@example.com',
            'phone' => '+60123456780',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $this->guard->assignRole('guard');
    }

    public function test_resident_can_register_visitor(): void
    {
        $response = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'visitor',
                'visitor_name' => 'John Smith',
                'contact_number' => '+60198765432',
                'visit_date' => now()->addDay()->format('Y-m-d'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['visitor', 'qr_token']]);
    }

    public function test_resident_can_list_visitors(): void
    {
        $response = $this->actingAs($this->resident)
            ->getJson('/api/v1/resident/visitors');

        $response->assertOk()->assertJsonPath('success', true);
    }

    public function test_resident_can_retrieve_active_visitor_qr_token_later(): void
    {
        $createResponse = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'visitor',
                'visitor_name' => 'Later QR',
                'contact_number' => '+60198765432',
                'visit_date' => now()->addDay()->format('Y-m-d'),
            ]);

        $visitorUuid = $createResponse->json('data.visitor.uuid');
        $originalToken = $createResponse->json('data.qr_token');

        $response = $this->actingAs($this->resident)
            ->getJson("/api/v1/resident/visitors/{$visitorUuid}/qr");

        $response->assertOk()
            ->assertJsonPath('data.qr_token', $originalToken)
            ->assertJsonMissingPath('data.id');
    }

    public function test_resident_cannot_retrieve_qr_token_after_check_in(): void
    {
        $createResponse = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'delivery',
                'visitor_name' => 'Checked In QR',
                'contact_number' => '+60198765433',
                'visit_date' => now()->format('Y-m-d'),
            ]);

        $visitorUuid = $createResponse->json('data.visitor.uuid');

        $this->actingAs($this->guard)
            ->postJson("/api/v1/guard/visitors/{$visitorUuid}/check-in");

        $response = $this->actingAs($this->resident)
            ->getJson("/api/v1/resident/visitors/{$visitorUuid}/qr");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_guard_can_validate_qr_and_check_in(): void
    {
        $createResponse = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'delivery',
                'visitor_name' => 'Courier',
                'contact_number' => '+60111111111',
                'visit_date' => now()->format('Y-m-d'),
            ]);

        $qrToken = $createResponse->json('data.qr_token');
        $visitorUuid = $createResponse->json('data.visitor.uuid');

        $validateResponse = $this->actingAs($this->guard)
            ->postJson('/api/v1/guard/qr/validate', ['qr_token' => $qrToken]);

        $validateResponse->assertOk()
            ->assertJsonPath('data.result', 'valid_for_check_in')
            ->assertJsonPath('data.can_check_in', true)
            ->assertJsonPath('data.visitor.visitor_name', 'Courier');

        $checkInResponse = $this->actingAs($this->guard)
            ->postJson("/api/v1/guard/visitors/{$visitorUuid}/check-in");

        $checkInResponse->assertOk()
            ->assertJsonPath('data.status', 'Checked In');
    }

    public function test_checked_in_visitor_can_be_checked_out(): void
    {
        $createResponse = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'family',
                'visitor_name' => 'Mom',
                'contact_number' => '+60122222222',
                'visit_date' => now()->format('Y-m-d'),
            ]);

        $visitorUuid = $createResponse->json('data.visitor.uuid');

        $this->actingAs($this->guard)
            ->postJson("/api/v1/guard/visitors/{$visitorUuid}/check-in");

        $checkOutResponse = $this->actingAs($this->guard)
            ->postJson("/api/v1/guard/visitors/{$visitorUuid}/check-out");

        $checkOutResponse->assertOk()
            ->assertJsonPath('data.status', 'Checked Out');
    }

    public function test_resident_can_cancel_active_visitor(): void
    {
        $createResponse = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'contractor',
                'visitor_name' => 'Plumber',
                'contact_number' => '+60133333333',
                'visit_date' => now()->addDay()->format('Y-m-d'),
            ]);

        $visitorUuid = $createResponse->json('data.visitor.uuid');

        $cancelResponse = $this->actingAs($this->resident)
            ->postJson("/api/v1/resident/visitors/{$visitorUuid}/cancel", [
                'reason' => 'Plans changed',
            ]);

        $cancelResponse->assertOk()
            ->assertJsonPath('data.status', 'Cancelled');
    }

    public function test_invalid_qr_token_returns_error(): void
    {
        $response = $this->actingAs($this->guard)
            ->postJson('/api/v1/guard/qr/validate', ['qr_token' => 'invalid-token']);

        $response->assertOk()
            ->assertJsonPath('data.result', 'unknown_qr_code')
            ->assertJsonPath('data.can_check_in', false)
            ->assertJsonPath('data.visitor', null);

        $this->assertDatabaseHas('visitor_activity_logs', [
            'guard_id' => $this->guard->id,
            'action' => 'invalid_qr_scanned',
        ]);
    }

    public function test_guard_scan_logs_valid_qr_and_rejects_future_visit_for_check_in(): void
    {
        $createResponse = $this->actingAs($this->resident)
            ->postJson('/api/v1/resident/visitors', [
                'purpose' => 'visitor',
                'visitor_name' => 'Future Visitor',
                'contact_number' => '+60155555555',
                'visit_date' => now()->addDay()->format('Y-m-d'),
            ]);

        $qrToken = $createResponse->json('data.qr_token');
        $visitorUuid = $createResponse->json('data.visitor.uuid');

        $scanResponse = $this->actingAs($this->guard)
            ->postJson('/api/v1/guard/qr/validate', ['qr_token' => $qrToken]);

        $scanResponse->assertOk()
            ->assertJsonPath('data.result', 'not_valid_for_today')
            ->assertJsonPath('data.can_check_in', false);

        $checkInResponse = $this->actingAs($this->guard)
            ->postJson("/api/v1/guard/visitors/{$visitorUuid}/check-in");

        $checkInResponse->assertStatus(422);

        $this->assertTrue(
            VisitorActivityLog::where('guard_id', $this->guard->id)
                ->where('action', 'entry_rejected')
                ->exists()
        );
    }
}
