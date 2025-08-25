<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Public holidays for 2025
        $holidays = [
            [
                'holiday_name' => 'New Year\'s Day',
                'holiday_date' => '2025-01-01',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Martin Luther King Jr. Day',
                'holiday_date' => '2025-01-20',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Presidents\' Day',
                'holiday_date' => '2025-02-17',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Memorial Day',
                'holiday_date' => '2025-05-26',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Independence Day',
                'holiday_date' => '2025-07-04',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Labor Day',
                'holiday_date' => '2025-09-01',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Thanksgiving Day',
                'holiday_date' => '2025-11-27',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holiday_name' => 'Christmas Day',
                'holiday_date' => '2025-12-25',
                'holiday_type' => 'PUBLIC',
                'department_id' => null,
                'country_code' => 'US',
                'is_recurring' => true,
                'is_active' => true,
                'year' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('holidays')->insert($holidays);
    }
}
