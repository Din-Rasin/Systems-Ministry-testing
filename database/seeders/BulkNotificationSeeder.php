<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class BulkNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        // If we don't have enough data, return early
        if ($users->isEmpty()) {
            echo "Not enough data to seed notifications. Please run UserSeeder first.\n";
            return;
        }

        // Generate 50 notification records
        for ($i = 0; $i < 50; $i++) {
            // Randomly select a user
            $user = $users->random();

            // Random notification type
            $types = [
                'request_submitted',
                'request_approved',
                'request_rejected',
                'pending_approval',
                'workflow_completed',
                'system_alert',
                'reminder'
            ];
            $type = $types[array_rand($types)];

            // Generate data based on type
            $data = $this->generateNotificationData($type);

            // Randomly decide if notification is read (30% chance of being read)
            $isRead = rand(1, 100) <= 30;

            // Create the notification
            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'data' => $data,
                'read_at' => $isRead ? now()->subDays(rand(1, 30)) : null,
            ]);
        }

        echo "50 notifications have been successfully seeded.\n";
    }

    /**
     * Generate notification data based on type
     */
    private function generateNotificationData($type)
    {
        switch ($type) {
            case 'request_submitted':
                return [
                    'request_id' => rand(1, 1000),
                    'request_type' => rand(0, 1) ? 'leave' : 'mission',
                    'message' => 'Your request has been submitted successfully.',
                ];

            case 'request_approved':
                return [
                    'request_id' => rand(1, 1000),
                    'request_type' => rand(0, 1) ? 'leave' : 'mission',
                    'approved_by' => $this->getRandomUserName(),
                    'message' => 'Your request has been approved.',
                ];

            case 'request_rejected':
                return [
                    'request_id' => rand(1, 1000),
                    'request_type' => rand(0, 1) ? 'leave' : 'mission',
                    'rejected_by' => $this->getRandomUserName(),
                    'comments' => $this->getRandomRejectionComment(),
                    'message' => 'Your request has been rejected.',
                ];

            case 'pending_approval':
                return [
                    'request_id' => rand(1, 1000),
                    'request_type' => rand(0, 1) ? 'leave' : 'mission',
                    'requester' => $this->getRandomUserName(),
                    'message' => 'You have a pending approval request.',
                ];

            case 'workflow_completed':
                return [
                    'request_id' => rand(1, 1000),
                    'request_type' => rand(0, 1) ? 'leave' : 'mission',
                    'status' => ['approved', 'rejected'][rand(0, 1)],
                    'message' => 'Your request workflow has been completed.',
                ];

            case 'system_alert':
                return [
                    'alert_type' => ['maintenance', 'update', 'security'][rand(0, 2)],
                    'message' => $this->getRandomSystemAlert(),
                ];

            case 'reminder':
                return [
                    'reminder_type' => ['leave_balance', 'pending_request', 'meeting'][rand(0, 2)],
                    'message' => $this->getRandomReminder(),
                ];

            default:
                return [
                    'message' => 'You have a new notification.',
                ];
        }
    }

    /**
     * Get a random user name
     */
    private function getRandomUserName()
    {
        $names = [
            'John Smith', 'Jane Doe', 'Michael Johnson', 'Sarah Williams', 'David Brown',
            'Lisa Davis', 'Robert Miller', 'Jennifer Wilson', 'James Moore', 'Patricia Taylor',
            'Thomas Anderson', 'Linda Thomas', 'Christopher Jackson', 'Elizabeth White',
            'Daniel Harris', 'Maria Martin', 'Matthew Thompson', 'Susan Garcia', 'Anthony Martinez',
            'Karen Robinson', 'Mark Clark', 'Nancy Rodriguez', 'Steven Lewis', 'Betty Lee'
        ];

        return $names[array_rand($names)];
    }

    /**
     * Get a random rejection comment
     */
    private function getRandomRejectionComment()
    {
        $comments = [
            'Does not meet current requirements',
            'Insufficient justification provided',
            'Requires additional documentation',
            'Not approved at this time',
            'Further review needed',
            'Does not align with policies',
            'Request incomplete',
            'More information required'
        ];

        return $comments[array_rand($comments)];
    }

    /**
     * Get a random system alert message
     */
    private function getRandomSystemAlert()
    {
        $alerts = [
            'System maintenance scheduled for tonight',
            'New security update available',
            'Performance improvements deployed',
            'System update in progress',
            'Security patch applied successfully',
            'Database optimization completed',
            'New features now available',
            'System backup completed'
        ];

        return $alerts[array_rand($alerts)];
    }

    /**
     * Get a random reminder message
     */
    private function getRandomReminder()
    {
        $reminders = [
            'Check your leave balance',
            'Pending request requires your attention',
            'Meeting scheduled for tomorrow',
            'Deadline approaching for submission',
            'Annual review coming up',
            'Training session reminder',
            'Policy update notification',
            'Performance review scheduled'
        ];

        return $reminders[array_rand($reminders)];
    }
}
