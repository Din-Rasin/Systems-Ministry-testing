<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DepartmentRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create departments
        $departments = [
            ['name' => 'IT', 'description' => 'Information Technology Department'],
            ['name' => 'Sales', 'description' => 'Sales Department'],
            ['name' => 'HR', 'description' => 'Human Resources Department'],
            ['name' => 'Finance', 'description' => 'Finance Department'],
            ['name' => 'Management', 'description' => 'Management Department'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['name' => $department['name']],
                $department
            );
        }

        // Create roles
        $roles = [
            ['name' => 'User', 'description' => 'Regular User'],
            ['name' => 'Team Leader', 'description' => 'Team Leader'],
            ['name' => 'HR Manager', 'description' => 'Human Resources Manager'],
            ['name' => 'CFO', 'description' => 'Chief Financial Officer'],
            ['name' => 'CEO', 'description' => 'Chief Executive Officer'],
            ['name' => 'Department Administrator', 'description' => 'Department Administrator'],
            ['name' => 'System Administrator', 'description' => 'System Administrator'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                $role
            );
        }

        // Create admin user
        $adminUser = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $userId = DB::table('users')->updateOrInsert(
            ['email' => $adminUser['email']],
            $adminUser
        );

        // Get the admin user ID
        $adminUserId = DB::table('users')->where('email', 'admin@example.com')->value('id');

        // Get department and role IDs
        $itDepartmentId = DB::table('departments')->where('name', 'IT')->value('id');
        $systemAdminRoleId = DB::table('roles')->where('name', 'System Administrator')->value('id');

        // Assign admin user to IT department with System Administrator role
        if ($adminUserId && $itDepartmentId && $systemAdminRoleId) {
            DB::table('user_roles')->updateOrInsert(
                [
                    'user_id' => $adminUserId,
                    'department_id' => $itDepartmentId,
                ],
                [
                    'role_id' => $systemAdminRoleId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
