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
            'Admin Operasional',
            'Admin Marketing',
            'Mitra Lokal',
            'Wisatawan',
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, $guard);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findByName('Super Admin', $guard)->syncPermissions($permissions);

        Role::findByName('Admin Operasional', $guard)->syncPermissions([
            'manage_products',
            'manage_transactions',
            'view_reports',
        ]);

        Role::findByName('Admin Marketing', $guard)->syncPermissions([
            'manage_products',
            'view_reports',
        ]);

        Role::findByName('Mitra Lokal', $guard)->syncPermissions([
            'manage_products',
            'manage_transactions',
        ]);

        Role::findByName('Wisatawan', $guard)->syncPermissions([
            'booking_ticket',
        ]);
    }
}
