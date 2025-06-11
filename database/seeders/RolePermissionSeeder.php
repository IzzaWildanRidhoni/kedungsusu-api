<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users CRUD
            'user.create',
            'user.read',
            'user.update',
            'user.delete',

            // Products CRUD
            'product.create',
            'product.read',
            'product.update',
            'product.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin Role
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permissions); // semua permission

        // User Role
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo([
            'product.read',
        ]);
    }
}
