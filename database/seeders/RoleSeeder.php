<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'System Administrator',
                'description' => 'Full system access and user management',
                'is_active' => true,
            ],
            [
                'name' => 'Department Administrator',
                'description' => 'Department-level administration and workflow management',
                'is_active' => true,
            ],
            [
                'name' => 'Team Leader',
                'description' => 'Team leadership and approval authority',
                'is_active' => true,
            ],
            [
                'name' => 'HR Manager',
                'description' => 'Human Resources management and approval',
                'is_active' => true,
            ],
            [
                'name' => 'CFO',
                'description' => 'Chief Financial Officer with financial approval authority',
                'is_active' => true,
            ],
            [
                'name' => 'CEO',
                'description' => 'Chief Executive Officer with ultimate approval authority',
                'is_active' => true,
            ],
            [
                'name' => 'Employee',
                'description' => 'Regular employee with basic access',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}