<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.approve',
            'users.reject',
            'users.suspend',

            // Visitors
            'visitors.view',
            'visitors.create',
            'visitors.cancel',
            'visitors.check-in',
            'visitors.check-out',

            // Facilities
            'facilities.view',
            'facilities.create',
            'facilities.edit',
            'facilities.manage',

            // Bookings
            'bookings.view',
            'bookings.create',
            'bookings.approve',
            'bookings.reject',
            'bookings.cancel',

            // Notifications
            'notifications.view',
            'notifications.create',
            'notifications.manage',

            // Settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $propertyAdmin = Role::create(['name' => 'property_admin']);
        $propertyAdmin->givePermissionTo(
            collect($permissions)->reject(fn (string $p) => $p === 'settings.edit')->toArray()
        );

        $guard = Role::create(['name' => 'guard']);
        $guard->givePermissionTo([
            'visitors.view',
            'visitors.check-in',
            'visitors.check-out',
        ]);

        $resident = Role::create(['name' => 'resident']);
        $resident->givePermissionTo([
            'visitors.view',
            'visitors.create',
            'visitors.cancel',
            'bookings.view',
            'bookings.create',
            'bookings.cancel',
            'facilities.view',
            'notifications.view',
        ]);
    }
}
