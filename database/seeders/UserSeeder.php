<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
        $users = [
            // System Admin
            [
                'name' => 'System Administrator',
                'email' => 'admin@company.com',
                'employee_id' => 'EMP001',
                'password' => Hash::make('password'),
                'roles' => ['system_admin'],
                'departments' => [],
            ],
            // CEO
            [
                'name' => 'John CEO',
                'email' => 'ceo@company.com',
                'employee_id' => 'EMP002',
                'password' => Hash::make('password'),
                'roles' => ['ceo'],
                'departments' => [],
            ],
            // CFO
            [
                'name' => 'Jane CFO',
                'email' => 'cfo@company.com',
                'employee_id' => 'EMP003',
                'password' => Hash::make('password'),
                'roles' => ['cfo'],
                'departments' => ['FINANCE'],
            ],
            // HR Manager
            [
                'name' => 'Bob HR Manager',
                'email' => 'hr@company.com',
                'employee_id' => 'EMP004',
                'password' => Hash::make('password'),
                'roles' => ['hr_manager'],
                'departments' => ['HR'],
            ],
            // IT Team Leader
            [
                'name' => 'Alice IT Leader',
                'email' => 'it-leader@company.com',
                'employee_id' => 'EMP005',
                'password' => Hash::make('password'),
                'roles' => ['team_leader'],
                'departments' => ['IT'],
            ],
            // IT Employee
            [
                'name' => 'Charlie IT Dev',
                'email' => 'charlie@company.com',
                'employee_id' => 'EMP006',
                'password' => Hash::make('password'),
                'roles' => ['employee'],
                'departments' => ['IT'],
            ],
            // Sales Team Leader
            [
                'name' => 'David Sales Leader',
                'email' => 'sales-leader@company.com',
                'employee_id' => 'EMP007',
                'password' => Hash::make('password'),
                'roles' => ['team_leader'],
                'departments' => ['SALES'],
            ],
            // Sales Employee
            [
                'name' => 'Eve Sales Rep',
                'email' => 'eve@company.com',
                'employee_id' => 'EMP008',
                'password' => Hash::make('password'),
                'roles' => ['employee'],
                'departments' => ['SALES'],
            ],
            // Department Admin IT
            [
                'name' => 'Frank IT Admin',
                'email' => 'it-admin@company.com',
                'employee_id' => 'EMP009',
                'password' => Hash::make('password'),
                'roles' => ['dept_admin'],
                'departments' => ['IT'],
            ],
            // Department Admin Sales
            [
                'name' => 'Grace Sales Admin',
                'email' => 'sales-admin@company.com',
                'employee_id' => 'EMP010',
                'password' => Hash::make('password'),
                'roles' => ['dept_admin'],
                'departments' => ['SALES'],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'employee_id' => $userData['employee_id'],
                    'password' => $userData['password'],
                    'is_active' => true,
                ]
            );

            // Assign roles
            foreach ($userData['roles'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    // For department-specific roles, attach with department
                    if (!empty($userData['departments'])) {
                        foreach ($userData['departments'] as $deptCode) {
                            $department = Department::where('code', $deptCode)->first();
                            if ($department) {
                                $user->roles()->syncWithoutDetaching([
                                    $role->id => ['department_id' => $department->id]
                                ]);
                            }
                        }
                    } else {
                        // Global roles (CEO, System Admin)
                        $user->roles()->syncWithoutDetaching([
                            $role->id => ['department_id' => null]
                        ]);
                    }
                }
            }

            // Assign to departments
            foreach ($userData['departments'] as $deptCode) {
                $department = Department::where('code', $deptCode)->first();
                if ($department) {
                    $user->departments()->syncWithoutDetaching([
                        $department->id => ['is_primary' => true]
                    ]);
                }
            }
        }

        // Update department managers
        $this->updateDepartmentManagers();

        $this->command->info('Users seeded successfully.');
    }

    private function updateDepartmentManagers(): void
    {
        // Set IT manager
        $itDept = Department::where('code', 'IT')->first();
        $itLeader = User::where('email', 'it-leader@company.com')->first();
        if ($itDept && $itLeader) {
            $itDept->update(['manager_id' => $itLeader->id]);
        }

        // Set Sales manager
        $salesDept = Department::where('code', 'SALES')->first();
        $salesLeader = User::where('email', 'sales-leader@company.com')->first();
        if ($salesDept && $salesLeader) {
            $salesDept->update(['manager_id' => $salesLeader->id]);
        }

        // Set HR manager
        $hrDept = Department::where('code', 'HR')->first();
        $hrManager = User::where('email', 'hr@company.com')->first();
        if ($hrDept && $hrManager) {
            $hrDept->update(['manager_id' => $hrManager->id]);
        }

        // Set Finance manager
        $financeDept = Department::where('code', 'FINANCE')->first();
        $cfo = User::where('email', 'cfo@company.com')->first();
        if ($financeDept && $cfo) {
            $financeDept->update(['manager_id' => $cfo->id]);
        }
    }
}
