<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    /**
     * Seed roles & permissions (Spatie).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $permissions = [
            'manage_users',
            'manage_products',
            'manage_transactions',
            'view_reports',
            'booking_ticket',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, $guard);
        }

        $roles = [
            'Super Admin',
            'Mitra',
            'Customer',
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, $guard);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findByName('Super Admin', $guard)->syncPermissions($permissions);

        Role::findByName('Mitra', $guard)->syncPermissions([
            'manage_products',
            'manage_transactions',
            'view_reports',
        ]);

        // Customer adalah guest (tidak login) untuk saat ini, jadi tidak perlu assign permission ke user.
    }
}
