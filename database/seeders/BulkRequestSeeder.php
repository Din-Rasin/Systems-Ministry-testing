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

class BulkRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users, workflows, and leave types
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

            // Randomly decide if this is a leave or mission request (60% leave, 40% mission)
            $requestType = rand(1, 100) <= 60 ? 'leave' : 'mission';

            // Random status with realistic distribution
            $statuses = [
                'pending' => 40,
                'approved' => 35,
                'rejected' => 10,
                'in_progress' => 10,
                'completed' => 5
            ];
            $status = $this->getWeightedRandomStatus($statuses);

            // Create the request
            $request = Request::create([
                'user_id' => $user->id,
                'type' => $requestType,
                'status' => $status,
                'workflow_id' => $workflow->id,
                'current_step_id' => $currentStep ? $currentStep->id : null,
                'data' => $this->generateRequestData($requestType),
                'submitted_at' => now()->subDays(rand(1, 60)),
                'decision_at' => in_array($status, ['approved', 'rejected', 'completed']) ? now()->subDays(rand(1, 30)) : null,
                'decision_by' => in_array($status, ['approved', 'rejected', 'completed']) ? $users->random()->id : null,
            ]);

            // Create related records based on request type
            if ($requestType === 'leave') {
                $this->createLeaveRequest($request, $leaveTypes);
            } else {
                $this->createMissionRequest($request);
            }
        }

        echo "50 requests have been successfully seeded.\n";
    }

    /**
     * Generate request data based on type
     */
    private function generateRequestData($type)
    {
        if ($type === 'leave') {
            return [
                'description' => $this->getRandomLeaveDescription(),
                'urgency' => ['low', 'medium', 'high'][rand(0, 2)],
            ];
        } else {
            return [
                'description' => $this->getRandomMissionDescription(),
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
        $startDate = now()->addDays(rand(1, 60));
        $endDate = $startDate->clone()->addDays(rand(1, 20));

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
        $startDate = now()->addDays(rand(1, 60));
        $endDate = $startDate->clone()->addDays(rand(1, 30));

        DB::table('mission_requests')->insert([
            'request_id' => $request->id,
            'destination' => $this->generateDestination(),
            'purpose' => $this->generateMissionPurpose(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budget' => rand(50000, 500000) / 100, // 500.00 to 5000.00
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
            'Educational purposes',
            'Wedding anniversary',
            'Religious observance',
            'Volunteer work',
            'Personal development',
            'Visiting family'
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Generate a random destination
     */
    private function generateDestination()
    {
        $destinations = [
            'New York', 'London', 'Tokyo', 'Paris', 'Sydney', 'Dubai', 'Singapore', 'Bangkok',
            'Kuala Lumpur', 'Hong Kong', 'Seoul', 'Beijing', 'Mumbai', 'Frankfurt', 'Amsterdam',
            'Toronto', 'Berlin', 'Madrid', 'Rome', 'Vienna', 'Zurich', 'Oslo', 'Stockholm',
            'Copenhagen', 'Helsinki', 'Brussels', 'Prague', 'Budapest', 'Warsaw', 'Athens',
            'Lisbon', 'Barcelona', 'Munich', 'Milan', 'Dublin', 'Edinburgh', 'Manchester',
            'Birmingham', 'Glasgow', 'Liverpool', 'Leeds', 'Sheffield', 'Bristol', 'Cardiff',
            'Belfast', 'Moscow', 'St. Petersburg', 'Cairo', 'Cape Town', 'Nairobi', 'Lagos',
            'Mumbai', 'Bangalore', 'Delhi', 'Kolkata', 'Chennai', 'Hyderabad', 'Pune',
            'Rio de Janeiro', 'São Paulo', 'Buenos Aires', 'Santiago', 'Lima', 'Bogotá',
            'Mexico City', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia',
            'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville'
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
            'Strategic planning session',
            'Vendor evaluation',
            'System implementation',
            'Business development',
            'Competitive analysis',
            'Regulatory compliance',
            'Risk assessment',
            'Performance review',
            'Team building workshop',
            'Product demonstration',
            'Customer onboarding'
        ];

        return $purposes[array_rand($purposes)];
    }

    /**
     * Get a weighted random status based on defined weights
     */
    private function getWeightedRandomStatus($weights)
    {
        $totalWeight = array_sum($weights);
        $randomNumber = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weights as $status => $weight) {
            $currentWeight += $weight;
            if ($randomNumber <= $currentWeight) {
                return $status;
            }
        }

        // Fallback to pending if something goes wrong
        return 'pending';
    }

    /**
     * Generate a random leave description
     */
    private function getRandomLeaveDescription()
    {
        $descriptions = [
            'Requesting time off for personal reasons',
            'Need leave for family obligations',
            'Planning a short vacation',
            'Medical leave for health checkup',
            'Personal days to manage household affairs',
            'Taking time off to recharge',
            'Leave for personal development activities',
            'Time off for family emergency',
            'Requesting annual leave',
            'Need break for mental wellness'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate a random mission description
     */
    private function getRandomMissionDescription()
    {
        $descriptions = [
            'Business trip to meet with key clients',
            'Mission to attend important industry conference',
            'Travel for training and professional development',
            'Site visit to evaluate new opportunities',
            'Meeting with partners for strategic initiatives',
            'Technical assessment at client location',
            'Project review and consultation',
            'Market research and competitive analysis',
            'Vendor evaluation and selection process',
            'Implementation of new systems and processes'
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
