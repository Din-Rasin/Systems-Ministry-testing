<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Request as WorkflowRequest;
use App\Models\LeaveRequest;
use App\Models\MissionRequest;
use App\Models\LeaveType;
use App\Http\Requests\LeaveRequestRequest;
use App\Http\Requests\MissionRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\WorkflowEngine;
use App\Services\NotificationService;
use App\Services\FileUploadService;
use App\Exceptions\WorkflowException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

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
    public function index(Request $request)
    {
        try {
            $cacheKey = "requests_index_" . Auth::id() . "_" . md5(serialize($request->all()));

            $data = Cache::remember($cacheKey, 300, function () use ($request) {
                $query = WorkflowRequest::with(['user', 'workflow', 'currentStep.role']);

                // Apply user-based filtering
                $query->when(!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                    // Regular users can only see their own requests
                    return $query->where('user_id', Auth::id());
                });

                $query->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                    // Admins can see all requests in their department
                    return $query->whereHas('user', function ($subQuery) {
                        $subQuery->where('department_id', Auth::user()->department_id);
                    });
                });

                // Apply search and filters
                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($subQuery) use ($search) {
                              $subQuery->where('name', 'like', "%{$search}%");
                          })
                          ->orWhereHas('workflow', function ($subQuery) use ($search) {
                              $subQuery->where('name', 'like', "%{$search}%");
                          });
                    });
                }

                if ($request->has('type') && $request->input('type') !== '') {
                    $query->where('type', $request->input('type'));
                }

                if ($request->has('status') && $request->input('status') !== '') {
                    $query->where('status', $request->input('status'));
                }

                if ($request->has('date_from') && $request->input('date_from') !== '') {
                    $query->whereDate('created_at', '>=', $request->input('date_from'));
                }

                if ($request->has('date_to') && $request->input('date_to') !== '') {
                    $query->whereDate('created_at', '<=', $request->input('date_to'));
                }

                // Get filter options for the view
                $types = WorkflowRequest::select('type')->distinct()->pluck('type');
                $statuses = WorkflowRequest::select('status')->distinct()->pluck('status');

                $requests = $query->orderBy('created_at', 'desc')->paginate(10);

                return compact('requests', 'types', 'statuses');
            });

            return view('requests.index', $data);
        } catch (\Exception $e) {
            Log::error('Request index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')->with('error', 'Unable to load requests. Please try again later.');
        }
    }

    /**
     * Show the form for creating a leave request.
     */
    public function createLeave()
    {
        try {
            $cacheKey = "leave_types_active";
            $leaveTypes = Cache::remember($cacheKey, 3600, function () {
                return LeaveType::where('is_active', true)->get();
            });

            return view('requests.create-leave', compact('leaveTypes'));
        } catch (\Exception $e) {
            Log::error('Create leave request error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('requests.index')->with('error', 'Unable to load leave request form. Please try again later.');
        }
    }

    /**
     * Show the form for creating a mission request.
     */
    public function createMission()
    {
        try {
            return view('requests.create-mission');
        } catch (\Exception $e) {
            Log::error('Create mission request error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('requests.index')->with('error', 'Unable to load mission request form. Please try again later.');
        }
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function storeLeave(LeaveRequestRequest $request)
    {
        try {
            $validatedData = $request->validated();

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
            if (isset($validatedData['supporting_document']) && $validatedData['supporting_document'] instanceof UploadedFile) {
                $uploadResult = $this->fileUploadService->uploadFile($validatedData['supporting_document'], 'documents');

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

            // Clear cache for this user's requests
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Leave request submitted successfully.');
        } catch (WorkflowException $e) {
            Log::error('Workflow exception in leave request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Workflow Error: ' . $e->getMessage())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Leave request submission error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit leave request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store a newly created mission request in storage.
     */
    public function storeMission(MissionRequestRequest $request)
    {
        try {
            $validatedData = $request->validated();

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
            if (isset($validatedData['supporting_document']) && $validatedData['supporting_document'] instanceof UploadedFile) {
                $uploadResult = $this->fileUploadService->uploadFile($validatedData['supporting_document'], 'documents');

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

            // Clear cache for this user's requests
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Mission request submitted successfully.');
        } catch (WorkflowException $e) {
            Log::error('Workflow exception in mission request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Workflow Error: ' . $e->getMessage())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Mission request submission error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit mission request: ' . $e->getMessage())
                ->withInput();
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
                abort(403);
            }

            $cacheKey = "request_show_" . $request->id;
            $requestData = Cache::remember($cacheKey, 300, function () use ($request) {
                $request->load(['user', 'workflow', 'currentStep.role', 'approvals.approver', 'leaveRequest', 'missionRequest']);
                return $request;
            });

            return view('requests.show', compact('requestData'));
        } catch (\Exception $e) {
            Log::error('Request show error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('requests.index')->with('error', 'Unable to load request. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkflowRequest $request)
    {
        try {
            // Only allow editing for draft requests by the owner
            if ($request->status !== 'draft' || Auth::id() !== $request->user_id) {
                abort(403);
            }

            if ($request->type === 'leave') {
                $cacheKey = "leave_types_active";
                $leaveTypes = Cache::remember($cacheKey, 3600, function () {
                    return LeaveType::where('is_active', true)->get();
                });

                $request->load('leaveRequest');
                return view('requests.edit-leave', compact('request', 'leaveTypes'));
            } else {
                $request->load('missionRequest');
                return view('requests.edit-mission', compact('request'));
            }
        } catch (\Exception $e) {
            Log::error('Request edit error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('requests.index')->with('error', 'Unable to load edit request form. Please try again later.');
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
                abort(403);
            }

            if ($workflowRequest->type === 'leave') {
                return $this->updateLeaveRequest($workflowRequest, $request);
            } else {
                return $this->updateMissionRequest($workflowRequest, $request);
            }
        } catch (\Exception $e) {
            Log::error('Request update error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $workflowRequest->id,
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a leave request.
     */
    protected function updateLeaveRequest(WorkflowRequest $workflowRequest, Request $request)
    {
        try {
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
            if (isset($validatedData['supporting_document']) && $validatedData['supporting_document'] instanceof UploadedFile) {
                $uploadResult = $this->fileUploadService->uploadFile($validatedData['supporting_document'], 'documents');

                if ($uploadResult['success']) {
                    $leaveRequest->supporting_document = $uploadResult['path'];
                    $leaveRequest->save();
                } else {
                    throw new \Exception('File upload failed: ' . $uploadResult['error']);
                }
            }

            // Clear cache for this request
            Cache::forget("request_show_" . $workflowRequest->id);
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Leave request updated successfully.');
        } catch (\Exception $e) {
            Log::error('Leave request update error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $workflowRequest->id,
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update leave request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a mission request.
     */
    protected function updateMissionRequest(WorkflowRequest $workflowRequest, Request $request)
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
            if (isset($validatedData['supporting_document']) && $validatedData['supporting_document'] instanceof UploadedFile) {
                $uploadResult = $this->fileUploadService->uploadFile($validatedData['supporting_document'], 'documents');

                if ($uploadResult['success']) {
                    $missionRequest->supporting_document = $uploadResult['path'];
                    $missionRequest->save();
                } else {
                    throw new \Exception('File upload failed: ' . $uploadResult['error']);
                }
            }

            // Clear cache for this request
            Cache::forget("request_show_" . $workflowRequest->id);
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Mission request updated successfully.');
        } catch (\Exception $e) {
            Log::error('Mission request update error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $workflowRequest->id,
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update mission request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkflowRequest $request)
    {
        try {
            // Only allow deleting draft requests by the owner
            if ($request->status !== 'draft' || Auth::id() !== $request->user_id) {
                abort(403);
            }

            // Delete related records
            if ($request->type === 'leave') {
                $request->leaveRequest()->delete();
            } else {
                $request->missionRequest()->delete();
            }

            $request->approvals()->delete();
            $request->delete();

            // Clear cache for this request
            Cache::forget("request_show_" . $request->id);
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Request deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Request delete error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete request: ' . $e->getMessage());
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
                abort(403);
            }

            // Initialize workflow
            $this->workflowEngine->initializeWorkflow($request);

            // Clear cache for this request
            Cache::forget("request_show_" . $request->id);
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Request submitted successfully.');
        } catch (WorkflowException $e) {
            Log::error('Workflow exception in request submission: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Workflow Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Request submission error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a request.
     */
    public function cancel(WorkflowRequest $request)
    {
        try {
            // Only allow canceling pending requests by the owner
            if ($request->status !== 'pending' || Auth::id() !== $request->user_id) {
                abort(403);
            }

            // Cancel the request
            $request->status = 'cancelled';
            $request->save();

            // Clear cache for this request
            Cache::forget("request_show_" . $request->id);
            Cache::forget("requests_index_" . Auth::id() . "_");

            return redirect()->route('requests.index')
                ->with('success', 'Request cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Request cancel error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to cancel request: ' . $e->getMessage());
        }
    }

    /**
     * Store a message for a request.
     */
    public function storeMessage(WorkflowRequest $request, Request $httpRequest)
    {
        try {
            $validatedData = $httpRequest->validate([
                'message' => 'required|string|max:1000',
            ]);

            // Create message
            $request->messages()->create([
                'user_id' => Auth::id(),
                'message' => $validatedData['message'],
            ]);

            // Clear cache for this request
            Cache::forget("request_show_" . $request->id);

            return redirect()->back()
                ->with('success', 'Message added successfully.');
        } catch (\Exception $e) {
            Log::error('Request message error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'input' => $httpRequest->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to add message: ' . $e->getMessage())
                ->withInput();
        }
    }
}
