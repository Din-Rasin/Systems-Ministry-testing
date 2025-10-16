<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Yearly vacation leave',
                'max_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave for illness',
                'max_days' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'description' => 'Emergency personal leave',
                'max_days' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Maternity leave for new mothers',
                'max_days' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Paternity leave for new fathers',
                'max_days' => 14,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}