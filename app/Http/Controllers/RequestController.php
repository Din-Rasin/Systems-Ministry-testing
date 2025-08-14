<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as WorkflowRequest;
use App\Models\Department;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RequestController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->middleware('auth');
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of requests
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->requests()->with(['workflow.department']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('request_number', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new request
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $department = $user->primaryDepartment();
        
        if (!$department) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to a department to submit requests.');
        }

        $type = $request->get('type', 'leave');
        
        return view('requests.create', compact('type', 'department'));
    }

    /**
     * Store a newly created request
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        $validatedData = $request->validate([
            'type' => 'required|in:leave,mission',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'destination' => 'nullable|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string',
        ]);

        try {
            $workflowRequest = $this->workflowService->submitRequest($validatedData, $user);
            
            return redirect()->route('requests.show', $workflowRequest)
                ->with('success', 'Request submitted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified request
     */
    public function show(WorkflowRequest $request)
    {
        $user = Auth::user();
        
        // Check if user can view this request
        if ($request->user_id !== $user->id && !$user->hasRole('system_admin') && !$user->hasRole('hr_manager')) {
            abort(403, 'Unauthorized to view this request.');
        }

        $request->load(['user', 'workflow.department', 'workflow.steps']);
        
        // Get workflow progress
        $progress = $this->workflowService->getWorkflowProgress($request);
        
        // Get approval history
        $approvalHistory = $this->workflowService->getApprovalHistory($request);
        
        return view('requests.show', compact('request', 'progress', 'approvalHistory'));
    }

    /**
     * Show the form for editing the specified request
     */
    public function edit(WorkflowRequest $request)
    {
        $user = Auth::user();
        
        // Only the request owner can edit and only if it's pending
        if ($request->user_id !== $user->id || !$request->isPending()) {
            abort(403, 'You cannot edit this request.');
        }

        return view('requests.edit', compact('request'));
    }

    /**
     * Update the specified request
     */
    public function update(Request $request, WorkflowRequest $workflowRequest)
    {
        $user = Auth::user();
        
        // Only the request owner can update and only if it's pending
        if ($workflowRequest->user_id !== $user->id || !$workflowRequest->isPending()) {
            abort(403, 'You cannot edit this request.');
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'destination' => 'nullable|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string',
        ]);

        $workflowRequest->update($validatedData);

        return redirect()->route('requests.show', $workflowRequest)
            ->with('success', 'Request updated successfully!');
    }

    /**
     * Cancel the specified request
     */
    public function destroy(WorkflowRequest $request)
    {
        $user = Auth::user();
        
        try {
            $this->workflowService->cancelRequest($request, $user);
            
            return redirect()->route('requests.index')
                ->with('success', 'Request cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download request details as PDF
     */
    public function download(WorkflowRequest $request)
    {
        $user = Auth::user();
        
        // Check if user can view this request
        if ($request->user_id !== $user->id && !$user->hasRole('system_admin') && !$user->hasRole('hr_manager')) {
            abort(403, 'Unauthorized to download this request.');
        }

        // TODO: Implement PDF generation
        return response()->json(['message' => 'PDF download feature will be implemented']);
    }
}
