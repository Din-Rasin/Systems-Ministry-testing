<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'IT Department',
                'description' => 'Information Technology Department responsible for system development and maintenance',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Department',
                'description' => 'Sales and Marketing Department responsible for business development',
                'is_active' => true,
            ],
            [
                'name' => 'HR Department',
                'description' => 'Human Resources Department responsible for employee management',
                'is_active' => true,
            ],
            [
                'name' => 'Finance Department',
                'description' => 'Finance Department responsible for financial operations',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}