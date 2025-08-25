<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'template_name' => 'Leave Request Submitted',
                'template_type' => 'REQUEST_SUBMITTED',
                'subject_template' => 'New Leave Request from {{user_name}}',
                'message_template' => 'A new leave request has been submitted by {{user_name}} for {{leave_type}} from {{start_date}} to {{end_date}}.',
                'placeholders' => json_encode(['user_name', 'leave_type', 'start_date', 'end_date']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'template_name' => 'Mission Request Submitted',
                'template_type' => 'REQUEST_SUBMITTED',
                'subject_template' => 'New Mission Request from {{user_name}}',
                'message_template' => 'A new mission request has been submitted by {{user_name}} to {{destination}} from {{start_date}} to {{end_date}}.',
                'placeholders' => json_encode(['user_name', 'destination', 'start_date', 'end_date']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'template_name' => 'Approval Needed',
                'template_type' => 'APPROVAL_NEEDED',
                'subject_template' => 'Approval Needed for {{request_type}} Request',
                'message_template' => 'You have a new {{request_type}} request to approve from {{user_name}}.',
                'placeholders' => json_encode(['request_type', 'user_name']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'template_name' => 'Request Approved',
                'template_type' => 'REQUEST_APPROVED',
                'subject_template' => 'Your {{request_type}} Request has been Approved',
                'message_template' => 'Your {{request_type}} request has been approved by {{approver_name}}.',
                'placeholders' => json_encode(['request_type', 'approver_name']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'template_name' => 'Request Rejected',
                'template_type' => 'REQUEST_REJECTED',
                'subject_template' => 'Your {{request_type}} Request has been Rejected',
                'message_template' => 'Your {{request_type}} request has been rejected by {{approver_name}}. Reason: {{rejection_reason}}',
                'placeholders' => json_encode(['request_type', 'approver_name', 'rejection_reason']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('notification_templates')->insert($templates);
    }
}
