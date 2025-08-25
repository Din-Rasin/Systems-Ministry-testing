<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'max_days' => 20,
                'description' => 'Regular annual leave for employees',
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'max_days' => 10,
                'description' => 'Leave for illness or medical appointments',
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'max_days' => 5,
                'description' => 'Leave for emergency situations',
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'max_days' => 90,
                'description' => 'Leave for maternity purposes',
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'max_days' => 10,
                'description' => 'Leave for paternity purposes',
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }
    }
}
