<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Request;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users, workflows, and workflow steps
        $users = User::all();
        $workflows = Workflow::all();
        $leaveTypes = LeaveType::all();

        // If we don't have enough data, return early
        if ($users->isEmpty() || $workflows->isEmpty() || $leaveTypes->isEmpty()) {
            echo "Not enough data to seed requests. Please run other seeders first.\n";
            return;
        }

        // Generate 50 request records
        for ($i = 0; $i < 50; $i++) {
            // Randomly select a user
            $user = $users->random();

            // Randomly select a workflow
            $workflow = $workflows->random();

            // Get the first step of the workflow as the current step
            $currentStep = $workflow->steps()->orderBy('step_number')->first();

            // Randomly decide if this is a leave or mission request
            $requestType = rand(0, 1) ? 'leave' : 'mission';

            // Random status
            $statuses = ['pending', 'approved', 'rejected', 'in_progress', 'completed'];
            $status = $statuses[array_rand($statuses)];

            // Create the request
            $request = Request::create([
                'user_id' => $user->id,
                'type' => $requestType,
                'status' => $status,
                'workflow_id' => $workflow->id,
                'current_step_id' => $currentStep ? $currentStep->id : null,
                'data' => $this->generateRequestData($requestType),
                'submitted_at' => now()->subDays(rand(1, 30)),
                'decision_at' => in_array($status, ['approved', 'rejected', 'completed']) ? now()->subDays(rand(1, 15)) : null,
                'decision_by' => in_array($status, ['approved', 'rejected', 'completed']) ? $users->random()->id : null,
            ]);

            // Create related records based on request type
            if ($requestType === 'leave') {
                $this->createLeaveRequest($request, $leaveTypes);
            } else {
                $this->createMissionRequest($request);
            }
        }
    }

    /**
     * Generate request data based on type
     */
    private function generateRequestData($type)
    {
        if ($type === 'leave') {
            return [
                'description' => 'Leave request for personal reasons',
                'urgency' => ['low', 'medium', 'high'][rand(0, 2)],
            ];
        } else {
            return [
                'description' => 'Mission request for business trip',
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
            ];
        }
    }

    /**
     * Create a leave request record
     */
    private function createLeaveRequest($request, $leaveTypes)
    {
        $leaveType = $leaveTypes->random();
        $startDate = now()->addDays(rand(1, 30));
        $endDate = $startDate->clone()->addDays(rand(1, 10));

        DB::table('leave_requests')->insert([
            'request_id' => $request->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $this->generateLeaveReason(),
            'supporting_document' => rand(0, 1) ? 'document_' . rand(1, 100) . '.pdf' : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create a mission request record
     */
    private function createMissionRequest($request)
    {
        $startDate = now()->addDays(rand(1, 30));
        $endDate = $startDate->clone()->addDays(rand(1, 15));

        DB::table('mission_requests')->insert([
            'request_id' => $request->id,
            'destination' => $this->generateDestination(),
            'purpose' => $this->generateMissionPurpose(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budget' => rand(1000, 10000) / 100,
            'supporting_document' => rand(0, 1) ? 'mission_doc_' . rand(1, 50) . '.pdf' : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Generate a random leave reason
     */
    private function generateLeaveReason()
    {
        $reasons = [
            'Personal vacation',
            'Family emergency',
            'Medical appointment',
            'Home maintenance',
            'Mental health day',
            'Relocation',
            'Jury duty',
            'Bereavement',
            'Maternity/paternity leave',
            'Educational purposes'
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Generate a random destination
     */
    private function generateDestination()
    {
        $destinations = [
            'New York',
            'London',
            'Tokyo',
            'Paris',
            'Sydney',
            'Dubai',
            'Singapore',
            'Bangkok',
            'Kuala Lumpur',
            'Hong Kong',
            'Seoul',
            'Beijing',
            'Mumbai',
            'Frankfurt',
            'Amsterdam'
        ];

        return $destinations[array_rand($destinations)];
    }

    /**
     * Generate a random mission purpose
     */
    private function generateMissionPurpose()
    {
        $purposes = [
            'Client meeting and project discussion',
            'Training and skill development',
            'Conference attendance',
            'Product launch event',
            'Market research and analysis',
            'Partnership negotiation',
            'Technical support and troubleshooting',
            'Quality assurance review',
            'Compliance audit',
            'Strategic planning session'
        ];

        return $purposes[array_rand($purposes)];
    }
}
