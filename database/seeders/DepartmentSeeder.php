<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'Technology development and infrastructure management',
                'manager_id' => null, // Will be set after users are created
                'is_active' => true,
            ],
            [
                'name' => 'Sales & Marketing',
                'code' => 'SALES',
                'description' => 'Sales operations and marketing activities',
                'manager_id' => null, // Will be set after users are created
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Human resources management and employee relations',
                'manager_id' => null, // Will be set after users are created
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'code' => 'FINANCE',
                'description' => 'Financial planning, analysis, and accounting',
                'manager_id' => null, // Will be set after users are created
                'is_active' => true,
            ],
        ];

        foreach ($departments as $deptData) {
            Department::updateOrCreate(
                ['code' => $deptData['code']],
                $deptData
            );
        }

        $this->command->info('Departments seeded successfully.');
    }
}
