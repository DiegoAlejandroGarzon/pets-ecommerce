<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $customerRole = \Spatie\Permission\Models\Role::create(['name' => 'customer']);

        // Create Admin User
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@pets.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        ]);

        $admin->assignRole($adminRole);
    }
}
