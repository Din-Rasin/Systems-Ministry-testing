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
use App\Services\NotificationService;
use App\Services\FileUploadService;

class RequestController extends Controller
{
    protected $workflowEngine;
    protected $notificationService;
    protected $fileUploadService;

    public function __construct(
        WorkflowEngine $workflowEngine,
        NotificationService $notificationService,
        FileUploadService $fileUploadService
    ) {
        $this->workflowEngine = $workflowEngine;
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $requests = WorkflowRequest::with(['user', 'workflow', 'currentStep.role'])
                ->when(!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                    // Regular users can only see their own requests
                    return $query->where('user_id', Auth::id());
                })
                ->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                    // Admins can see all requests in their department
                    return $query->whereHas('user', function ($subQuery) {
                        $subQuery->where('department_id', Auth::user()->department_id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve requests: ' . $e->getMessage(),
            ], 500);
        }
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
                $uploadResult = $this->fileUploadService->uploadFile($request->file('supporting_document'), 'documents');

                if ($uploadResult['success']) {
                    $leaveRequest->supporting_document = $uploadResult['path'];
                    $leaveRequest->save();
                } else {
                    throw new \Exception('File upload failed: ' . $uploadResult['error']);
                }
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
                $uploadResult = $this->fileUploadService->uploadFile($request->file('supporting_document'), 'documents');

                if ($uploadResult['success']) {
                    $missionRequest->supporting_document = $uploadResult['path'];
                    $missionRequest->save();
                } else {
                    throw new \Exception('File upload failed: ' . $uploadResult['error']);
                }
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
        try {
            // Authorize the request
            if (!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator'])
                && Auth::id() !== $request->user_id) {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WorkflowRequest $workflowRequest, Request $request)
    {
        try {
            // Only allow updating for draft requests by the owner
            if ($workflowRequest->status !== 'draft' || Auth::id() !== $workflowRequest->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            if ($workflowRequest->type === 'leave') {
                return $this->updateLeaveRequest($workflowRequest, $request);
            } else {
                return $this->updateMissionRequest($workflowRequest, $request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a leave request.
     */
    protected function updateLeaveRequest(WorkflowRequest $workflowRequest, Request $request)
    {
        $validatedData = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        $workflowRequest->data = [
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'reason' => $validatedData['reason'],
        ];
        $workflowRequest->save();

        $leaveRequest = $workflowRequest->leaveRequest;
        $leaveRequest->update([
            'leave_type_id' => $validatedData['leave_type_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'reason' => $validatedData['reason'],
        ]);

        // Handle file upload if present
        if ($request->hasFile('supporting_document')) {
            $uploadResult = $this->fileUploadService->uploadFile($request->file('supporting_document'), 'documents');

            if ($uploadResult['success']) {
                $leaveRequest->supporting_document = $uploadResult['path'];
                $leaveRequest->save();
            } else {
                throw new \Exception('File upload failed: ' . $uploadResult['error']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully.',
            'data' => $workflowRequest->load(['user', 'workflow', 'currentStep.role']),
        ]);
    }

    /**
     * Update a mission request.
     */
    protected function updateMissionRequest(WorkflowRequest $workflowRequest, Request $request)
    {
        $validatedData = $request->validate([
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        $workflowRequest->data = [
            'destination' => $validatedData['destination'],
            'purpose' => $validatedData['purpose'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'budget' => $validatedData['budget'],
        ];
        $workflowRequest->save();

        $missionRequest = $workflowRequest->missionRequest;
        $missionRequest->update([
            'destination' => $validatedData['destination'],
            'purpose' => $validatedData['purpose'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'budget' => $validatedData['budget'],
        ]);

        // Handle file upload if present
        if ($request->hasFile('supporting_document')) {
            $uploadResult = $this->fileUploadService->uploadFile($request->file('supporting_document'), 'documents');

            if ($uploadResult['success']) {
                $missionRequest->supporting_document = $uploadResult['path'];
                $missionRequest->save();
            } else {
                throw new \Exception('File upload failed: ' . $uploadResult['error']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mission request updated successfully.',
            'data' => $workflowRequest->load(['user', 'workflow', 'currentStep.role']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkflowRequest $request)
    {
        try {
            // Only allow deleting draft requests by the owner
            if ($request->status !== 'draft' || Auth::id() !== $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            // Delete related records
            if ($request->type === 'leave') {
                $request->leaveRequest()->delete();
            } else {
                $request->missionRequest()->delete();
            }

            $request->approvals()->delete();
            $request->delete();

            return response()->json([
                'success' => true,
                'message' => 'Request deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a draft request to start the workflow.
     */
    public function submit(WorkflowRequest $request)
    {
        try {
            // Only allow submitting draft requests by the owner
            if ($request->status !== 'draft' || Auth::id() !== $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            // Initialize workflow
            $this->workflowEngine->initializeWorkflow($request);

            return response()->json([
                'success' => true,
                'message' => 'Request submitted successfully.',
                'data' => $request->load(['user', 'workflow', 'currentStep.role']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit request: ' . $e->getMessage(),
            ], 422);
        }
    }
}
