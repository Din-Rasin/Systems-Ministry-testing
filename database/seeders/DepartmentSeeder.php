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
                'name' => 'IT Department',
                'description' => 'Information Technology Department',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Department',
                'description' => 'Sales and Marketing Department',
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Human Resources Department',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance and Accounting Department',
                'is_active' => true,
            ],
            [
                'name' => 'Executive',
                'description' => 'Executive Management',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['name' => $department['name']],
                $department
            );
        }
    }
}
