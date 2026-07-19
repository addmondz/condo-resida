<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'resident', 'guard_name' => 'web']);
        Role::create(['name' => 'guard', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'property_admin', 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createProperty(): array
    {
        $property = Property::create(['name' => 'Test Property']);
        $block = Block::create(['property_id' => $property->id, 'name' => 'A']);
        $unit = Unit::create(['block_id' => $block->id, 'name' => '101', 'floor' => 1]);

        return [$property, $block, $unit];
    }

    public function test_user_can_register(): void
    {
        [$property, $block, $unit] = $this->createProperty();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'property_uuid' => $property->uuid,
            'block_uuid' => $block->uuid,
            'unit_uuid' => $unit->uuid,
            'resident_type' => 'owner',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'status' => 'pending']);
    }

    public function test_registration_rejects_unit_that_does_not_belong_to_selected_block(): void
    {
        [$property, $block] = $this->createProperty();

        $otherProperty = Property::create(['name' => 'Other Property']);
        $otherBlock = Block::create(['property_id' => $otherProperty->id, 'name' => 'B']);
        $otherUnit = Unit::create(['block_id' => $otherBlock->id, 'name' => '202', 'floor' => 2]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Invalid Unit',
            'email' => 'invalid-unit@example.com',
            'phone' => '+60123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'property_uuid' => $property->uuid,
            'block_uuid' => $block->uuid,
            'unit_uuid' => $otherUnit->uuid,
            'resident_type' => 'owner',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_uuid']);

        $this->assertDatabaseMissing('users', ['email' => 'invalid-unit@example.com']);
    }

    public function test_user_can_login(): void
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+60123456789',
            'password' => bcrypt('password123'),
            'status' => UserStatus::Approved,
        ]);
        $user->assignRole('resident');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_pending_user_can_login_with_pending_status(): void
    {
        $user = User::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'phone' => '+60123456789',
            'password' => bcrypt('password123'),
            'status' => UserStatus::Pending,
        ]);
        $user->assignRole('resident');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.status', 'pending');
    }

    public function test_pending_user_cannot_access_resident_functions(): void
    {
        $user = User::create([
            'name' => 'Pending Resident',
            'email' => 'pending-resident@example.com',
            'phone' => '+60123456789',
            'password' => bcrypt('password123'),
            'status' => UserStatus::Pending,
        ]);
        $user->assignRole('resident');

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/resident/profile')
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+60123456789',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $user->assignRole('resident');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()->assertJsonPath('success', true);
    }
}
