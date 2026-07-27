<?php

namespace Database\Seeders;

use App\Enums\ApprovalAction;
use App\Enums\ResidentType;
use App\Enums\UserStatus;
use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserApproval;
use App\Models\UserUnitAssignment;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $property = Property::where('name', 'Broadleaf Residence')->firstOrFail();
        $block12A = Block::where('name', '12A')->where('property_id', $property->id)->firstOrFail();
        $unit184G = Unit::where('name', '184G')->where('block_id', $block12A->id)->firstOrFail();

        // 1. Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password',
            'status' => UserStatus::Approved,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // 2. Property Admin
        $propertyAdmin = User::create([
            'name' => 'Property Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'status' => UserStatus::Approved,
            'property_id' => $property->id,
            'email_verified_at' => now(),
        ]);
        $propertyAdmin->assignRole('property_admin');

        // 3. Guard
        $guard = User::create([
            'name' => 'Security Guard',
            'email' => 'guard@example.com',
            'password' => 'password',
            'status' => UserStatus::Approved,
            'property_id' => $property->id,
            'email_verified_at' => now(),
        ]);
        $guard->assignRole('guard');

        // 4. Approved Resident - assigned to unit 184G in block 12A
        $resident = User::create([
            'name' => 'John Resident',
            'email' => 'resident@example.com',
            'password' => 'password',
            'status' => UserStatus::Approved,
            'resident_type' => ResidentType::Owner,
            'email_verified_at' => now(),
        ]);
        $resident->assignRole('resident');

        UserUnitAssignment::create([
            'user_id' => $resident->id,
            'unit_id' => $unit184G->id,
            'property_id' => $property->id,
            'is_primary' => true,
            'assigned_at' => now(),
        ]);

        // Approval record for approved resident
        UserApproval::create([
            'user_id' => $resident->id,
            'action' => ApprovalAction::Approved,
            'previous_status' => UserStatus::Pending->value,
            'new_status' => UserStatus::Approved->value,
            'reason' => 'Ownership documents verified.',
            'performed_by' => $propertyAdmin->id,
        ]);

        // 5. Pending Resident
        $pendingResident = User::create([
            'name' => 'Jane Pending',
            'email' => 'pending@example.com',
            'password' => 'password',
            'status' => UserStatus::Pending,
            'resident_type' => ResidentType::Tenant,
        ]);
        $pendingResident->assignRole('resident');

        // 6. Rejected Resident
        $rejectedResident = User::create([
            'name' => 'Bob Rejected',
            'email' => 'rejected@example.com',
            'password' => 'password',
            'status' => UserStatus::Rejected,
        ]);
        $rejectedResident->assignRole('resident');

        // Approval record for rejected resident
        UserApproval::create([
            'user_id' => $rejectedResident->id,
            'action' => ApprovalAction::Rejected,
            'previous_status' => UserStatus::Pending->value,
            'new_status' => UserStatus::Rejected->value,
            'reason' => 'Unable to verify tenancy agreement.',
            'performed_by' => $propertyAdmin->id,
        ]);
    }
}
