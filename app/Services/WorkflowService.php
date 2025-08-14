<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestApproval;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Exception;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /**
     * Submit a new request and initialize the workflow
     */
    public function submitRequest(array $requestData, User $user): Request
    {
        return DB::transaction(function () use ($requestData, $user) {
            // Get the appropriate workflow for the department and request type
            $department = $user->primaryDepartment();
            if (!$department) {
                throw new Exception('User must be assigned to a department to submit requests.');
            }

            $workflow = $department->getWorkflowForType($requestData['type']);
            if (!$workflow) {
                throw new Exception("No workflow found for {$requestData['type']} requests in {$department->name} department.");
            }

            // Create the request
            $request = Request::create(array_merge($requestData, [
                'user_id' => $user->id,
                'workflow_id' => $workflow->id,
                'status' => 'pending',
            ]));

            // Initialize the approval workflow
            $this->initializeApprovalWorkflow($request);

            return $request;
        });
    }

    /**
     * Initialize the approval workflow for a request
     */
    public function initializeApprovalWorkflow(Request $request): void
    {
        $workflow = $request->workflow;
        $steps = $workflow->activeSteps;

        foreach ($steps as $step) {
            RequestApproval::create([
                'request_id' => $request->id,
                'workflow_step_id' => $step->id,
                'status' => 'pending',
                'sequence' => $step->sequence,
            ]);
        }
    }

    /**
     * Process an approval/rejection for a request step
     */
    public function processApproval(Request $request, User $approver, string $action, ?string $comments = null): bool
    {
        if (!in_array($action, ['approve', 'reject'])) {
            throw new Exception('Invalid action. Must be "approve" or "reject".');
        }

        return DB::transaction(function () use ($request, $approver, $action, $comments) {
            // Get the current step that needs approval
            $currentStep = $request->getCurrentStep();
            
            if (!$currentStep) {
                throw new Exception('No pending approval step found for this request.');
            }

            // Check if the user can approve this step
            if (!$request->canBeApprovedBy($approver)) {
                throw new Exception('You are not authorized to approve this step.');
            }

            // Get the approval record for this step
            $approval = RequestApproval::where('request_id', $request->id)
                ->where('workflow_step_id', $currentStep->id)
                ->first();

            if (!$approval) {
                throw new Exception('Approval record not found.');
            }

            // Process the approval/rejection
            if ($action === 'approve') {
                $approval->approve($approver, $comments);
                
                // Check if this completes the workflow
                if ($this->isWorkflowComplete($request)) {
                    $request->approve();
                    $this->notifyRequestCompletion($request);
                } else {
                    $this->notifyNextApprover($request);
                }
            } else {
                $approval->reject($approver, $comments);
                $request->reject();
                $this->notifyRequestRejection($request);
            }

            return true;
        });
    }

    /**
     * Check if the workflow is complete for a request
     */
    public function isWorkflowComplete(Request $request): bool
    {
        return $request->workflow->isCompleteForRequest($request);
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(User $user)
    {
        return RequestApproval::where('status', 'pending')
            ->whereHas('workflowStep', function ($query) use ($user) {
                $query->where('approver_role', function ($roleQuery) use ($user) {
                    $roleQuery->select('name')
                        ->from('roles')
                        ->whereIn('id', function ($userRoleQuery) use ($user) {
                            $userRoleQuery->select('role_id')
                                ->from('role_user')
                                ->where('user_id', $user->id);
                        });
                });
            })
            ->whereHas('request', function ($query) {
                $query->where('status', 'pending');
            })
            ->with(['request.user', 'request.workflow.department', 'workflowStep'])
            ->get()
            ->filter(function ($approval) use ($user) {
                // Additional filtering to ensure user can approve based on department context
                $request = $approval->request;
                $userDepartment = $user->primaryDepartment();
                $requestDepartment = $request->workflow->department;
                
                // Check if user has the required role in the request's department or globally
                return $user->hasRole($approval->workflowStep->approver_role, $requestDepartment?->id) ||
                       $user->hasRole($approval->workflowStep->approver_role, null);
            });
    }

    /**
     * Get approval history for a request
     */
    public function getApprovalHistory(Request $request)
    {
        return $request->approvals()
            ->with(['workflowStep', 'approver'])
            ->orderBy('sequence')
            ->get();
    }

    /**
     * Cancel a request
     */
    public function cancelRequest(Request $request, User $user): bool
    {
        // Only the request submitter or system admin can cancel
        if ($request->user_id !== $user->id && !$user->hasRole('system_admin')) {
            throw new Exception('You are not authorized to cancel this request.');
        }

        if (!$request->isPending()) {
            throw new Exception('Only pending requests can be cancelled.');
        }

        $request->cancel();
        $this->notifyRequestCancellation($request);

        return true;
    }

    /**
     * Get workflow progress for a request
     */
    public function getWorkflowProgress(Request $request): array
    {
        $steps = $request->workflow->activeSteps;
        $approvals = $request->approvals()->with('workflowStep')->get()->keyBy('workflow_step_id');

        $progress = [];
        foreach ($steps as $step) {
            $approval = $approvals->get($step->id);
            $progress[] = [
                'step' => $step,
                'approval' => $approval,
                'status' => $approval ? $approval->status : 'pending',
                'is_current' => $approval && $approval->status === 'pending' && 
                              $this->isCurrentStep($request, $step),
            ];
        }

        return $progress;
    }

    /**
     * Check if a step is the current step for a request
     */
    private function isCurrentStep(Request $request, WorkflowStep $step): bool
    {
        $currentStep = $request->getCurrentStep();
        return $currentStep && $currentStep->id === $step->id;
    }

    /**
     * Notify next approver (placeholder for notification system)
     */
    private function notifyNextApprover(Request $request): void
    {
        // TODO: Implement email notification to next approver
        // This would typically send an email to users with the required role
    }

    /**
     * Notify request completion (placeholder for notification system)
     */
    private function notifyRequestCompletion(Request $request): void
    {
        // TODO: Implement email notification to request submitter
    }

    /**
     * Notify request rejection (placeholder for notification system)
     */
    private function notifyRequestRejection(Request $request): void
    {
        // TODO: Implement email notification to request submitter
    }

    /**
     * Notify request cancellation (placeholder for notification system)
     */
    private function notifyRequestCancellation(Request $request): void
    {
        // TODO: Implement email notification to relevant parties
    }

    /**
     * Get statistics for dashboard
     */
    public function getWorkflowStatistics(User $user): array
    {
        $stats = [
            'pending_requests' => 0,
            'approved_requests' => 0,
            'rejected_requests' => 0,
            'pending_approvals' => 0,
        ];

        // User's own requests
        if ($user->hasRole('employee') || $user->hasRole('team_leader')) {
            $userRequests = $user->requests();
            $stats['pending_requests'] = $userRequests->where('status', 'pending')->count();
            $stats['approved_requests'] = $userRequests->where('status', 'approved')->count();
            $stats['rejected_requests'] = $userRequests->where('status', 'rejected')->count();
        }

        // Pending approvals for this user
        $stats['pending_approvals'] = $this->getPendingApprovalsForUser($user)->count();

        return $stats;
    }
}