<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'resident', 'guard_name' => 'web']);
        Role::create(['name' => 'guard', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'property_admin', 'guard_name' => 'web']);
    }

    private function createUserWithRole(string $role): string
    {
        $user = User::create([
            'name' => ucfirst($role),
            'email' => "$role@example.com",
            'phone' => '+60123456789',
            'password' => bcrypt('password'),
            'status' => UserStatus::Approved,
        ]);
        $user->assignRole($role);

        return $user->createToken('auth')->plainTextToken;
    }

    public function test_resident_cannot_access_admin_routes(): void
    {
        $token = $this->createUserWithRole('resident');

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_resident_cannot_access_guard_routes(): void
    {
        $token = $this->createUserWithRole('resident');

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/guard/dashboard')
            ->assertStatus(403);
    }

    public function test_guard_cannot_access_admin_routes(): void
    {
        $token = $this->createUserWithRole('guard');

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_guard_cannot_access_resident_routes(): void
    {
        $token = $this->createUserWithRole('guard');

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/resident/visitors')
            ->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $token = $this->createUserWithRole('super_admin');

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/v1/resident/visitors')
            ->assertStatus(401);

        $this->getJson('/api/v1/guard/dashboard')
            ->assertStatus(401);

        $this->getJson('/api/v1/admin/dashboard')
            ->assertStatus(401);
    }
}
