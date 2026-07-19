<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserStatus;
use App\Models\Block;
use App\Models\Facility;
use App\Models\FacilityBlockedSlot;
use App\Models\FacilityBooking;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserUnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FacilityBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $resident;

    private User $admin;

    private Facility $facility;

    private string $residentToken;

    private string $adminToken;

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

        $this->facility = Facility::create([
            'property_id' => $property->id,
            'name' => 'Swimming Pool',
            'opening_time' => '08:00',
            'closing_time' => '22:00',
            'slot_duration' => 60,
            'max_bookings_per_resident' => 2,
            'advance_booking_days' => 7,
            'cancellation_hours' => 24,
            'is_active' => true,
            'is_under_maintenance' => false,
        ]);

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

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '+60123456780',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $this->admin->assignRole('super_admin');

        $this->residentToken = $this->resident->createToken('auth')->plainTextToken;
        $this->adminToken = $this->admin->createToken('auth')->plainTextToken;
    }

    public function test_resident_can_list_facilities(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->getJson('/api/v1/resident/facilities');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_resident_can_check_availability(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->getJson("/api/v1/resident/facilities/{$this->facility->uuid}/availability?date=".now()->addDay()->format('Y-m-d'));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $slots = $response->json('data');
        $this->assertNotEmpty($slots);
        $this->assertTrue($slots[0]['available']);
    }

    public function test_resident_can_create_booking(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->postJson('/api/v1/resident/bookings', [
                'facility_uuid' => $this->facility->uuid,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Pending');
    }

    public function test_admin_can_approve_booking(): void
    {
        $createResponse = $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->postJson('/api/v1/resident/bookings', [
                'facility_uuid' => $this->facility->uuid,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '15:00',
            ]);

        $bookingUuid = $createResponse->json('data.uuid');

        $this->assertTrue($this->admin->fresh()->hasRole('super_admin'));
        $this->assertNotEmpty($bookingUuid);

        auth()->forgetGuards();

        $approveResponse = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/bookings/{$bookingUuid}/approve");

        $approveResponse->assertOk()
            ->assertJsonPath('data.status', 'Approved');
    }

    public function test_conflicting_booking_is_rejected(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->postJson('/api/v1/resident/bookings', [
                'facility_uuid' => $this->facility->uuid,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->postJson('/api/v1/resident/bookings', [
                'facility_uuid' => $this->facility->uuid,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_approval_rejects_conflicting_approved_booking(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $approved = FacilityBooking::create([
            'facility_id' => $this->facility->id,
            'resident_id' => $this->resident->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => BookingStatus::Approved,
        ]);

        $pending = FacilityBooking::create([
            'facility_id' => $this->facility->id,
            'resident_id' => $this->resident->id,
            'booking_date' => $date,
            'start_time' => '10:30',
            'end_time' => '11:30',
            'status' => BookingStatus::Pending,
        ]);

        auth()->forgetGuards();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/bookings/{$pending->uuid}/approve");

        $response->assertStatus(422);
        $this->assertDatabaseHas('facility_bookings', [
            'id' => $approved->id,
            'status' => BookingStatus::Approved->value,
        ]);
        $this->assertDatabaseHas('facility_bookings', [
            'id' => $pending->id,
            'status' => BookingStatus::Pending->value,
        ]);
    }

    public function test_full_day_blocked_facility_date_has_no_available_slots(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        FacilityBlockedSlot::create([
            'facility_id' => $this->facility->id,
            'blocked_date' => $date,
            'reason' => 'Maintenance',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->residentToken}")
            ->getJson("/api/v1/resident/facilities/{$this->facility->uuid}/availability?date={$date}");

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
        $this->assertFalse(collect($response->json('data'))->contains('available', true));
    }

    public function test_admin_can_create_facility_blocked_slot(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        auth()->forgetGuards();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/facilities/{$this->facility->uuid}/blocked-slots", [
                'blocked_date' => $date,
                'start_time' => '12:00',
                'end_time' => '13:00',
                'reason' => 'Cleaning',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.blocked_date', $date);

        $this->assertDatabaseHas('facility_blocked_slots', [
            'facility_id' => $this->facility->id,
            'reason' => 'Cleaning',
        ]);
    }
}
