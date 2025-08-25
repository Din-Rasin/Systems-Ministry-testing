<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'User',
                'description' => 'Regular user with basic permissions',
                'is_active' => true,
            ],
            [
                'name' => 'Team Leader',
                'description' => 'Team leader with approval permissions',
                'is_active' => true,
            ],
            [
                'name' => 'HR Manager',
                'description' => 'Human Resources Manager',
                'is_active' => true,
            ],
            [
                'name' => 'CFO',
                'description' => 'Chief Financial Officer',
                'is_active' => true,
            ],
            [
                'name' => 'CEO',
                'description' => 'Chief Executive Officer',
                'is_active' => true,
            ],
            [
                'name' => 'Department Administrator',
                'description' => 'Department Administrator with management permissions',
                'is_active' => true,
            ],
            [
                'name' => 'System Administrator',
                'description' => 'System Administrator with full permissions',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
