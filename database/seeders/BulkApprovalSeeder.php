<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Approval;
use App\Models\Request;
use App\Models\User;
use App\Models\WorkflowStep;

class BulkApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all requests, users, and workflow steps
        $requests = Request::all();
        $users = User::all();
        $workflowSteps = WorkflowStep::all();

        // If we don't have enough data, return early
        if ($requests->isEmpty() || $users->isEmpty() || $workflowSteps->isEmpty()) {
            echo "Not enough data to seed approvals. Please run other seeders first.\n";
            return;
        }

        // Generate 50 approval records
        for ($i = 0; $i < 50; $i++) {
            // Randomly select a request
            $request = $requests->random();

            // Randomly select a workflow step for this request's workflow
            $stepsForRequest = $workflowSteps->where('workflow_id', $request->workflow_id);
            if ($stepsForRequest->isEmpty()) {
                continue;
            }

            $step = $stepsForRequest->random();

            // Randomly select an approver (must have the role required by the step)
            $requiredRole = $step->role;
            $approvers = $users->filter(function ($user) use ($requiredRole) {
                return $user->roles->contains($requiredRole);
            });

            if ($approvers->isEmpty()) {
                // If no users with the required role, use any user
                $approver = $users->random();
            } else {
                $approver = $approvers->random();
            }

            // Random status with realistic distribution
            $statuses = [
                'pending' => 40,
                'approved' => 45,
                'rejected' => 15
            ];
            $status = $this->getWeightedRandomStatus($statuses);

            // Create the approval
            Approval::create([
                'request_id' => $request->id,
                'approver_id' => $approver->id,
                'step_id' => $step->id,
                'status' => $status,
                'comments' => $status !== 'pending' ? $this->generateApprovalComment($status) : null,
                'approved_at' => $status !== 'pending' ? now()->subDays(rand(1, 30)) : null,
            ]);
        }

        echo "50 approvals have been successfully seeded.\n";
    }

    /**
     * Generate approval comment based on status
     */
    private function generateApprovalComment($status)
    {
        if ($status === 'approved') {
            $comments = [
                'Approved as requested.',
                'Request approved with no issues.',
                'All requirements met, approved.',
                'Good to proceed with this request.',
                'Approved after review of details.',
                'No concerns, approval granted.',
                'Request meets all criteria, approved.',
                'Approved for processing.',
                'Looks good, approved.',
                'Approved with confidence.'
            ];
        } else {
            $comments = [
                'More information needed before approval.',
                'Request does not meet current requirements.',
                'Please review and resubmit with corrections.',
                'Not approved at this time, pending further review.',
                'Insufficient justification provided.',
                'Requires additional documentation.',
                'Does not align with current policies.',
                'Rejected due to incomplete information.',
                'Further clarification required.',
                'Not approved, please contact for details.'
            ];
        }

        return $comments[array_rand($comments)];
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
}
