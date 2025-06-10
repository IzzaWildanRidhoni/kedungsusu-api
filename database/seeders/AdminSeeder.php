<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role admin jika belum ada
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Buat user admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@kedungsusu.com'],
            [
                'name' => 'Admin Kedung Susu',
                'password' => bcrypt('11111111') // ganti dengan password aman
            ]
        );

        // Assign role admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
