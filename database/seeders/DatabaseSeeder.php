<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RoleSeeder::class,
            LeaveTypeSeeder::class,
            UserSeeder::class,
            WorkflowSeeder::class,
            RequestSeeder::class,
            BulkUserSeeder::class,
            BulkRequestSeeder::class,
            BulkApprovalSeeder::class,
            BulkNotificationSeeder::class,
            NotificationTemplateSeeder::class,
            HolidaySeeder::class,
            SystemSettingSeeder::class,
        ]);

        // User::factory(10)->create();

        // Create a test user if one doesn't already exist
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
