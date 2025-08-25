<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $itDepartment = Department::where('name', 'IT Department')->first();
        $salesDepartment = Department::where('name', 'Sales Department')->first();
        $hrDepartment = Department::where('name', 'Human Resources')->first();
        $financeDepartment = Department::where('name', 'Finance')->first();
        $executiveDepartment = Department::where('name', 'Executive')->first();

        // Get roles
        $userRole = Role::where('name', 'User')->first();
        $teamLeaderRole = Role::where('name', 'Team Leader')->first();
        $hrManagerRole = Role::where('name', 'HR Manager')->first();
        $cfoRole = Role::where('name', 'CFO')->first();
        $ceoRole = Role::where('name', 'CEO')->first();
        $deptAdminRole = Role::where('name', 'Department Administrator')->first();
        $sysAdminRole = Role::where('name', 'System Administrator')->first();

        // Create users for IT Department
        $itUsers = [
            [
                'name' => 'John Developer',
                'email' => 'john.developer@example.com',
                'password' => Hash::make('password'),
                'roles' => [$userRole],
                'department' => $itDepartment,
            ],
            [
                'name' => 'Jane Team Lead',
                'email' => 'jane.teamlead@example.com',
                'password' => Hash::make('password'),
                'roles' => [$userRole, $teamLeaderRole],
                'department' => $itDepartment,
            ],
        ];

        // Create users for Sales Department
        $salesUsers = [
            [
                'name' => 'Mike Salesperson',
                'email' => 'mike.sales@example.com',
                'password' => Hash::make('password'),
                'roles' => [$userRole],
                'department' => $salesDepartment,
            ],
            [
                'name' => 'Sarah Sales Lead',
                'email' => 'sarah.saleslead@example.com',
                'password' => Hash::make('password'),
                'roles' => [$userRole, $teamLeaderRole],
                'department' => $salesDepartment,
            ],
        ];

        // Create admin users
        $adminUsers = [
            [
                'name' => 'HR Manager',
                'email' => 'hr.manager@example.com',
                'password' => Hash::make('password'),
                'roles' => [$hrManagerRole],
                'department' => $hrDepartment,
            ],
            [
                'name' => 'CFO User',
                'email' => 'cfo@example.com',
                'password' => Hash::make('password'),
                'roles' => [$cfoRole],
                'department' => $financeDepartment,
            ],
            [
                'name' => 'CEO User',
                'email' => 'ceo@example.com',
                'password' => Hash::make('password'),
                'roles' => [$ceoRole],
                'department' => $executiveDepartment,
            ],
            [
                'name' => 'System Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'roles' => [$sysAdminRole],
                'department' => $itDepartment,
            ],
        ];

        // Create all users
        $allUsers = array_merge($itUsers, $salesUsers, $adminUsers);

        foreach ($allUsers as $userData) {
            // Check if user already exists
            $user = User::where('email', $userData['email'])->first();

            if (!$user) {
                // Create user if they don't exist
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                ]);

                // Assign roles and department
                foreach ($userData['roles'] as $role) {
                    $user->roles()->attach($role, [
                        'department_id' => $userData['department']->id,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
