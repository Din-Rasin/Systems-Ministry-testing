<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'setting_key' => 'company_name',
                'setting_value' => '"Acme Corporation"',
                'data_type' => 'STRING',
                'category' => 'GENERAL',
                'description' => 'Name of the company',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'company_email',
                'setting_value' => '"info@acmecorp.com"',
                'data_type' => 'STRING',
                'category' => 'GENERAL',
                'description' => 'Company email address',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'company_phone',
                'setting_value' => '"+1-555-123-4567"',
                'data_type' => 'STRING',
                'category' => 'GENERAL',
                'description' => 'Company phone number',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'default_timezone',
                'setting_value' => '"America/New_York"',
                'data_type' => 'STRING',
                'category' => 'GENERAL',
                'description' => 'Default timezone for the application',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'enable_email_notifications',
                'setting_value' => 'true',
                'data_type' => 'BOOLEAN',
                'category' => 'NOTIFICATIONS',
                'description' => 'Enable email notifications for requests and approvals',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'enable_sms_notifications',
                'setting_value' => 'false',
                'data_type' => 'BOOLEAN',
                'category' => 'NOTIFICATIONS',
                'description' => 'Enable SMS notifications for requests and approvals',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'max_concurrent_leave_requests',
                'setting_value' => '5',
                'data_type' => 'INTEGER',
                'category' => 'LEAVE',
                'description' => 'Maximum number of concurrent leave requests per employee',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'min_notice_days_for_leave',
                'setting_value' => '7',
                'data_type' => 'INTEGER',
                'category' => 'LEAVE',
                'description' => 'Minimum notice days required for leave requests',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'max_mission_budget',
                'setting_value' => '10000',
                'data_type' => 'INTEGER',
                'category' => 'MISSION',
                'description' => 'Maximum budget allowed for mission requests without CFO approval',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'password_min_length',
                'setting_value' => '8',
                'data_type' => 'INTEGER',
                'category' => 'SECURITY',
                'description' => 'Minimum password length',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'password_requires_numbers',
                'setting_value' => 'true',
                'data_type' => 'BOOLEAN',
                'category' => 'SECURITY',
                'description' => 'Require numbers in passwords',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'password_requires_symbols',
                'setting_value' => 'true',
                'data_type' => 'BOOLEAN',
                'category' => 'SECURITY',
                'description' => 'Require symbols in passwords',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('system_settings')->insert($settings);
    }
}
