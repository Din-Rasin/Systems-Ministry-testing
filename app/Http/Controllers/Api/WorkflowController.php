<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request as WorkflowRequest;
use App\Models\LeaveRequest;
use App\Models\MissionRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WorkflowEngine;

class WorkflowController extends Controller
{
    protected $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = WorkflowRequest::with(['user', 'workflow', 'currentStep.role'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function storeLeave(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:1000',
                'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            ]);

            // Create the base request
            $workflowRequest = WorkflowRequest::create([
                'user_id' => Auth::id(),
                'type' => 'leave',
                'status' => 'draft',
                'data' => [
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'reason' => $validatedData['reason'] ?? null,
                ],
            ]);

            // Create the leave request details
            $leaveRequest = LeaveRequest::create([
                'request_id' => $workflowRequest->id,
                'leave_type_id' => $validatedData['leave_type_id'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'reason' => $validatedData['reason'] ?? null,
            ]);

            // Handle file upload if present
            if ($request->hasFile('supporting_document')) {
                $file = $request->file('supporting_document');
                $path = $file->store('documents', 'public');
                $leaveRequest->supporting_document = $path;
                $leaveRequest->save();
            }

            // Initialize workflow
            $workflowRequest->status = 'pending';
            $this->workflowEngine->initializeWorkflow($workflowRequest);

            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully.',
                'data' => $workflowRequest->load(['user', 'workflow', 'currentStep.role']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave request: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store a newly created mission request in storage.
     */
    public function storeMission(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'destination' => 'required|string|max:255',
                'purpose' => 'required|string|max:1000',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'budget' => 'required|numeric|min:0',
                'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            ]);

            // Create the base request
            $workflowRequest = WorkflowRequest::create([
                'user_id' => Auth::id(),
                'type' => 'mission',
                'status' => 'draft',
                'data' => [
                    'destination' => $validatedData['destination'],
                    'purpose' => $validatedData['purpose'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'budget' => $validatedData['budget'],
                ],
            ]);

            // Create the mission request details
            $missionRequest = MissionRequest::create([
                'request_id' => $workflowRequest->id,
                'destination' => $validatedData['destination'],
                'purpose' => $validatedData['purpose'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'budget' => $validatedData['budget'],
            ]);

            // Handle file upload if present
            if ($request->hasFile('supporting_document')) {
                $file = $request->file('supporting_document');
                $path = $file->store('documents', 'public');
                $missionRequest->supporting_document = $path;
                $missionRequest->save();
            }

            // Initialize workflow
            $workflowRequest->status = 'pending';
            $this->workflowEngine->initializeWorkflow($workflowRequest);

            return response()->json([
                'success' => true,
                'message' => 'Mission request submitted successfully.',
                'data' => $workflowRequest->load(['user', 'workflow', 'currentStep.role']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit mission request: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkflowRequest $request)
    {
        // Authorize the request
        if (Auth::id() !== $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        $request->load(['user', 'workflow', 'currentStep.role', 'approvals.approver', 'leaveRequest', 'missionRequest']);

        return response()->json([
            'success' => true,
            'data' => $request,
        ]);
    }

    /**
     * Get pending approvals for the authenticated user.
     */
    public function pendingApprovals()
    {
        $approvals = Auth::user()->approvals()
            ->with(['request.user', 'request.workflow', 'step.role'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    /**
     * Process an approval.
     */
    public function processApproval(Request $request, $approvalId)
    {
        try {
            $validatedData = $request->validate([
                'decision' => 'required|in:approved,rejected',
                'comments' => 'nullable|string|max:1000',
            ]);

            $approval = Auth::user()->approvals()->find($approvalId);

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval not found.',
                ], 404);
            }

            if ($approval->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval already processed.',
                ], 422);
            }

            // Process the approval
            $workflowRequest = $this->workflowEngine->processApproval(
                $approval,
                $validatedData['decision'],
                $validatedData['comments'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Approval processed successfully.',
                'data' => $workflowRequest->load(['user', 'workflow', 'currentStep.role']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process approval: ' . $e->getMessage(),
            ], 422);
        }
    }

     /**
      * Get workflow templates.
      */
     public function getTemplates()
     {
         try {
             $workflows = \App\Models\Workflow::with(['department', 'steps.role'])
                 ->where('is_active', true)
                 ->get();

             return response()->json([
                 'success' => true,
                 'data' => $workflows,
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to retrieve workflow templates: ' . $e->getMessage(),
             ], 500);
         }
     }

     /**
      * Get a specific workflow template by ID.
      */
     public function getTemplate($id)
     {
         try {
             $workflow = \App\Models\Workflow::with(['department', 'steps.role'])
                 ->where('is_active', true)
                 ->findOrFail($id);

             return response()->json([
                 'success' => true,
                 'data' => $workflow,
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to retrieve workflow template: ' . $e->getMessage(),
             ], 404);
         }
     }

     /**
      * Display a listing of workflows.
      */
     public function list()
     {
         try {
             $workflows = \App\Models\Workflow::with(['department', 'steps.role'])
                 ->when(Auth::user()->hasRole('System Administrator'), function ($query) {
                     // System admins can see all workflows
                     return $query;
                 })
                 ->when(!Auth::user()->hasRole('System Administrator'), function ($query) {
                     // Other users can only see workflows from their department
                     return $query->where('department_id', Auth::user()->department_id);
                 })
                 ->get();

             return response()->json([
                 'success' => true,
                 'data' => $workflows,
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to retrieve workflows: ' . $e->getMessage(),
             ], 500);
         }
     }

     /**
      * Store a newly created workflow in storage.
      */
     public function create(Request $request)
     {
         try {
             // Authorize the request
             if (!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator'])) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Unauthorized access.',
                 ], 403);
             }

             $validatedData = $request->validate([
                 'name' => 'required|string|max:255',
                 'department_id' => 'required|exists:departments,id',
                 'type' => 'required|string|max:50',
                 'description' => 'nullable|string',
                 'is_active' => 'boolean',
                 'steps' => 'array',
                 'steps.*.step_number' => 'required_with:steps|integer|min:1',
                 'steps.*.role_id' => 'required_with:steps|exists:roles,id',
                 'steps.*.description' => 'nullable|string',
             ]);

             $workflow = \App\Models\Workflow::create([
                 'name' => $validatedData['name'],
                 'department_id' => $validatedData['department_id'],
                 'type' => $validatedData['type'],
                 'description' => $validatedData['description'] ?? null,
                 'is_active' => $validatedData['is_active'] ?? true,
             ]);

             // Create workflow steps
             if (isset($validatedData['steps'])) {
                 foreach ($validatedData['steps'] as $stepData) {
                     \App\Models\WorkflowStep::create([
                         'workflow_id' => $workflow->id,
                         'step_number' => $stepData['step_number'],
                         'role_id' => $stepData['role_id'],
                         'description' => $stepData['description'] ?? null,
                     ]);
                 }
             }

             return response()->json([
                 'success' => true,
                 'message' => 'Workflow created successfully.',
                 'data' => $workflow->load(['department', 'steps.role']),
             ], 201);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to create workflow: ' . $e->getMessage(),
             ], 422);
         }
     }

     /**
      * Display the specified workflow.
      */
     public function view($id)
     {
         try {
             $workflow = \App\Models\Workflow::with(['department', 'steps.role'])
                 ->when(Auth::user()->hasRole('System Administrator'), function ($query) {
                     // System admins can see all workflows
                     return $query;
                 })
                 ->when(!Auth::user()->hasRole('System Administrator'), function ($query) {
                     // Other users can only see workflows from their department
                     return $query->where('department_id', Auth::user()->department_id);
                 })
                 ->findOrFail($id);

             return response()->json([
                 'success' => true,
                 'data' => $workflow,
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to retrieve workflow: ' . $e->getMessage(),
             ], 404);
         }
     }

     /**
      * Update the specified workflow in storage.
      */
     public function edit(Request $request, $id)
     {
         try {
             $workflow = \App\Models\Workflow::findOrFail($id);

             // Authorize the request
             if (!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']) ||
                 (!Auth::user()->hasRole('System Administrator') && $workflow->department_id !== Auth::user()->department_id)) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Unauthorized access.',
                 ], 403);
             }

             $validatedData = $request->validate([
                 'name' => 'string|max:255',
                 'department_id' => 'exists:departments,id',
                 'type' => 'string|max:50',
                 'description' => 'nullable|string',
                 'is_active' => 'boolean',
                 'steps' => 'array',
                 'steps.*.step_number' => 'required_with:steps|integer|min:1',
                 'steps.*.role_id' => 'required_with:steps|exists:roles,id',
                 'steps.*.description' => 'nullable|string',
             ]);

             $workflow->update([
                 'name' => $validatedData['name'] ?? $workflow->name,
                 'department_id' => $validatedData['department_id'] ?? $workflow->department_id,
                 'type' => $validatedData['type'] ?? $workflow->type,
                 'description' => $validatedData['description'] ?? $workflow->description,
                 'is_active' => $validatedData['is_active'] ?? $workflow->is_active,
             ]);

             // Delete existing steps
             $workflow->steps()->delete();

             // Create new workflow steps
             if (isset($validatedData['steps'])) {
                 foreach ($validatedData['steps'] as $stepData) {
                     \App\Models\WorkflowStep::create([
                         'workflow_id' => $workflow->id,
                         'step_number' => $stepData['step_number'],
                         'role_id' => $stepData['role_id'],
                         'description' => $stepData['description'] ?? null,
                     ]);
                 }
             }

             return response()->json([
                 'success' => true,
                 'message' => 'Workflow updated successfully.',
                 'data' => $workflow->load(['department', 'steps.role']),
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to update workflow: ' . $e->getMessage(),
             ], 422);
         }
     }

     /**
      * Remove the specified workflow from storage.
      */
     public function delete($id)
     {
         try {
             $workflow = \App\Models\Workflow::findOrFail($id);

             // Authorize the request
             if (!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']) ||
                 (!Auth::user()->hasRole('System Administrator') && $workflow->department_id !== Auth::user()->department_id)) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Unauthorized access.',
                 ], 403);
             }

             // Check if workflow is being used by any requests
             if ($workflow->requests()->exists()) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Cannot delete workflow that is being used by requests.',
                 ], 422);
             }

             $workflow->steps()->delete();
             $workflow->delete();

             return response()->json([
                 'success' => true,
                 'message' => 'Workflow deleted successfully.',
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to delete workflow: ' . $e->getMessage(),
             ], 422);
         }
     }

     /**
      * Store a newly created message in storage.
      */
     public function storeMessage(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'subject' => 'required|string|max:255',
                'content' => 'required|string|max:5000',
                'recipient_id' => 'nullable|exists:users,id',
                'attachment' => 'nullable|file|max:5120', // 5MB limit
            ]);

            // For simplicity, we'll store messages in the requests table with type 'message'
            $workflowRequest = WorkflowRequest::create([
                'user_id' => 1, // Use a default user ID for testing
                'type' => 'message',
                'status' => 'sent',
                'workflow_id' => null, // Messages don't require a workflow
                'data' => [
                    'subject' => $validatedData['subject'],
                    'content' => $validatedData['content'],
                    'recipient_id' => $validatedData['recipient_id'] ?? null,
                ],
            ]);

            // Handle file upload if present
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = $file->store('messages', 'public');
                // Update the request with attachment info
                $workflowRequest->data = array_merge($workflowRequest->data, [
                    'attachment' => $attachmentPath,
                ]);
                $workflowRequest->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'data' => $workflowRequest->load('user'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get workflow visualization data for a specific request.
     */
    public function getWorkflowVisualization($requestId)
    {
        try {
            // Find the request with all necessary relationships
            $workflowRequest = WorkflowRequest::with([
                'user',
                'workflow.steps.role',
                'approvals.approver',
                'currentStep.role',
                'leaveRequest',
                'missionRequest'
            ])->findOrFail($requestId);

            // Authorize the request
            if (!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator'])
                && Auth::id() !== $workflowRequest->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            // Get all users who can be approvers (for displaying who might approve in future steps)
            $approvers = \App\Models\User::where('department_id', $workflowRequest->user->department_id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'request' => $workflowRequest,
                    'workflowTemplate' => $workflowRequest->workflow,
                    'approvers' => $approvers
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve workflow visualization data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
