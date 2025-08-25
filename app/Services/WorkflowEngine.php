<?php

namespace App\Services;

use App\Models\Request as WorkflowRequest;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\Approval;
use App\Models\User;
use App\Services\NotificationService;
use App\Exceptions\WorkflowException;
use App\Exceptions\ApprovalException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Initialize a workflow for a new request
     */
    public function initializeWorkflow(WorkflowRequest $request)
    {
        try {
            // Get the appropriate workflow for this request
            $workflow = Workflow::where('department_id', $request->user->department_id)
                ->where('type', $request->type)
                ->where('is_active', true)
                ->first();

            if (!$workflow) {
                throw new WorkflowException('No active workflow found for this request type and department');
            }

            // Set the workflow for the request
            $request->workflow_id = $workflow->id;

            // Set the initial step
            $firstStep = $workflow->steps()->orderBy('step_number')->first();
            if ($firstStep) {
                $request->current_step_id = $firstStep->id;
                $request->status = 'pending';
                $request->submitted_at = now();
            }

            $request->save();

            // Create initial approvals for each step
            $this->createApprovalsForRequest($request);

            // Send notification to the requester
            $this->notificationService->sendRequestSubmittedNotification($request);

            // Send notification to the first approver
            $this->notifyFirstApprover($request);

            return $request;
        } catch (WorkflowException $e) {
            Log::error('Workflow initialization failed: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during workflow initialization: ' . $e->getMessage());
            throw new WorkflowException('An unexpected error occurred while initializing the workflow');
        }
    }

    /**
     * Create approvals for all steps in the workflow
     */
    protected function createApprovalsForRequest(WorkflowRequest $request)
    {
        try {
            $workflowSteps = $request->workflow->steps()->orderBy('step_number')->get();

            foreach ($workflowSteps as $step) {
                Approval::create([
                    'request_id' => $request->id,
                    'approver_id' => $this->getApproverForStep($step, $request->user),
                    'step_id' => $step->id,
                    'status' => 'pending',
                ]);
            }
        } catch (\Exception $e) {
            throw new WorkflowException('Failed to create approvals for the request: ' . $e->getMessage());
        }
    }

    /**
     * Get the approver for a specific step
     */
    protected function getApproverForStep(WorkflowStep $step, User $requester)
    {
        try {
            // If a specific approver is assigned to the step, use that
            if ($step->approver_id) {
                return $step->approver_id;
            }

            // Otherwise, find a user with the required role in the same department
            $approver = User::whereHas('roles', function ($query) use ($step) {
                $query->where('role_id', $step->role_id);
            })->whereHas('departments', function ($query) use ($requester) {
                $query->where('department_id', $requester->department_id);
            })->first();

            return $approver ? $approver->id : null;
        } catch (\Exception $e) {
            throw new WorkflowException('Failed to find approver for step: ' . $e->getMessage());
        }
    }

    /**
     * Process an approval decision
     */
    public function processApproval(Approval $approval, string $decision, string $comments = null)
    {
        try {
            DB::beginTransaction();

            // Update the approval
            $approval->status = $decision;
            $approval->comments = $comments;
            $approval->approved_at = now();
            $approval->save();

            $request = $approval->request;

            // If approved, move to next step or complete workflow
            if ($decision === 'approved') {
                $this->handleApproval($request, $approval);
                // Send approval notification
                $this->notificationService->sendRequestApprovedNotification($request, $approval->approver);
            }
            // If rejected, end workflow
            elseif ($decision === 'rejected') {
                $this->handleRejection($request, $approval, $comments);
                // Send rejection notification
                $this->notificationService->sendRequestRejectedNotification($request, $approval->approver, $comments);
            }

            DB::commit();

            return $request;
        } catch (ApprovalException $e) {
            DB::rollBack();
            Log::error('Approval processing failed: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unexpected error during approval processing: ' . $e->getMessage());
            throw new ApprovalException('An unexpected error occurred while processing the approval');
        }
    }

    /**
     * Handle approval and move to next step or complete workflow
     */
    protected function handleApproval(WorkflowRequest $request, Approval $currentApproval)
    {
        try {
            // Check if all previous steps are approved
            $previousStepsApproved = $this->arePreviousStepsApproved($request, $currentApproval->step);

            if (!$previousStepsApproved) {
                return;
            }

            // Get the next step
            $nextStep = $this->getNextStep($currentApproval->step);

            if ($nextStep) {
                // Move to next step
                $request->current_step_id = $nextStep->id;
                $request->save();

                // Notify next approver
                $this->notifyNextApprover($request, $nextStep);

                // Send pending approval notification
                $approval = $request->approvals()->where('step_id', $nextStep->id)->first();
                if ($approval) {
                    $this->notificationService->sendPendingApprovalNotification($approval);
                }
            } else {
                // Workflow completed
                $request->status = 'approved';
                $request->decision_at = now();
                $request->decision_by = $currentApproval->approver_id;
                $request->save();

                // Notify requester
                $this->notifyRequester($request, 'approved');

                // Send workflow completed notification
                $this->notificationService->sendWorkflowCompletedNotification($request);
            }
        } catch (\Exception $e) {
            throw new ApprovalException('Failed to handle approval: ' . $e->getMessage());
        }
    }

    /**
     * Handle rejection and end workflow
     */
    protected function handleRejection(WorkflowRequest $request, Approval $approval, string $comments = null)
    {
        try {
            $request->status = 'rejected';
            $request->decision_at = now();
            $request->decision_by = $approval->approver_id;
            $request->save();

            // Notify requester
            $this->notifyRequester($request, 'rejected', $comments);

            // Send workflow completed notification (rejected)
            $this->notificationService->sendWorkflowCompletedNotification($request);
        } catch (\Exception $e) {
            throw new ApprovalException('Failed to handle rejection: ' . $e->getMessage());
        }
    }

    /**
     * Check if all previous steps are approved
     */
    protected function arePreviousStepsApproved(WorkflowRequest $request, WorkflowStep $currentStep)
    {
        try {
            $previousSteps = $request->workflow->steps()
                ->where('step_number', '<', $currentStep->step_number)
                ->get();

            foreach ($previousSteps as $step) {
                $approval = $request->approvals()->where('step_id', $step->id)->first();
                if (!$approval || $approval->status !== 'approved') {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            throw new ApprovalException('Failed to check previous steps approval status: ' . $e->getMessage());
        }
    }

    /**
     * Get the next step in the workflow
     */
    protected function getNextStep(WorkflowStep $currentStep)
    {
        try {
            return WorkflowStep::where('workflow_id', $currentStep->workflow_id)
                ->where('step_number', '>', $currentStep->step_number)
                ->orderBy('step_number')
                ->first();
        } catch (\Exception $e) {
            throw new WorkflowException('Failed to get next step: ' . $e->getMessage());
        }
    }

    /**
     * Notify the first approver
     */
    protected function notifyFirstApprover(WorkflowRequest $request)
    {
        try {
            // Get the first step
            $firstStep = $request->workflow->steps()->orderBy('step_number')->first();

            if ($firstStep) {
                // Get the approval for the first step
                $approval = $request->approvals()->where('step_id', $firstStep->id)->first();

                if ($approval) {
                    // Send pending approval notification
                    $this->notificationService->sendPendingApprovalNotification($approval);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to notify first approver: ' . $e->getMessage());
        }
    }

    /**
     * Notify the next approver
     */
    protected function notifyNextApprover(WorkflowRequest $request, WorkflowStep $nextStep)
    {
        try {
            // Get the approval for the next step
            $approval = $request->approvals()->where('step_id', $nextStep->id)->first();

            if ($approval) {
                // Send pending approval notification
                $this->notificationService->sendPendingApprovalNotification($approval);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to notify next approver: ' . $e->getMessage());
        }
    }

    /**
     * Notify the requester
     */
    protected function notifyRequester(WorkflowRequest $request, string $status, string $comments = null)
    {
        try {
            if ($status === 'approved') {
                $this->notificationService->sendRequestApprovedNotification($request, $request->user);
            } elseif ($status === 'rejected') {
                $this->notificationService->sendRequestRejectedNotification($request, $request->user, $comments);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to notify requester: ' . $e->getMessage());
        }
    }
}
