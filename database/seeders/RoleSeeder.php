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
                'name' => 'employee',
                'display_name' => 'Employee',
                'description' => 'Regular employee who can submit requests',
                'permissions' => Role::getDefaultPermissions('employee'),
            ],
            [
                'name' => 'team_leader',
                'display_name' => 'Team Leader',
                'description' => 'Team leader who can approve team member requests',
                'permissions' => Role::getDefaultPermissions('team_leader'),
            ],
            [
                'name' => 'hr_manager',
                'display_name' => 'HR Manager',
                'description' => 'HR manager who handles leave approvals',
                'permissions' => Role::getDefaultPermissions('hr_manager'),
            ],
            [
                'name' => 'cfo',
                'display_name' => 'Chief Financial Officer',
                'description' => 'CFO who approves financial and mission requests',
                'permissions' => Role::getDefaultPermissions('cfo'),
            ],
            [
                'name' => 'ceo',
                'display_name' => 'Chief Executive Officer',
                'description' => 'CEO who provides final approvals for high-level requests',
                'permissions' => Role::getDefaultPermissions('ceo'),
            ],
            [
                'name' => 'dept_admin',
                'display_name' => 'Department Administrator',
                'description' => 'Department administrator who manages workflows and submits department-level requests',
                'permissions' => Role::getDefaultPermissions('dept_admin'),
            ],
            [
                'name' => 'system_admin',
                'display_name' => 'System Administrator',
                'description' => 'System administrator with full access to all features',
                'permissions' => Role::getDefaultPermissions('system_admin'),
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully.');
    }
}
