<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as WorkflowRequest;
use App\Models\RequestApproval;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->middleware('auth');
        $this->workflowService = $workflowService;
    }

    /**
     * Display pending approvals for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get pending approvals for this user
        $pendingApprovals = $this->workflowService->getPendingApprovalsForUser($user);
        
        // Filter by request type if specified
        if ($request->filled('type')) {
            $pendingApprovals = $pendingApprovals->filter(function ($approval) use ($request) {
                return $approval->request->type === $request->type;
            });
        }

        // Filter by department if specified
        if ($request->filled('department')) {
            $pendingApprovals = $pendingApprovals->filter(function ($approval) use ($request) {
                return $approval->request->workflow->department->code === $request->department;
            });
        }

        return view('approvals.index', compact('pendingApprovals'));
    }

    /**
     * Show approval details for a specific request
     */
    public function show(WorkflowRequest $request)
    {
        $user = Auth::user();
        
        // Check if user can approve this request
        if (!$request->canBeApprovedBy($user)) {
            abort(403, 'You are not authorized to approve this request.');
        }

        $request->load(['user', 'workflow.department', 'workflow.steps']);
        
        // Get workflow progress
        $progress = $this->workflowService->getWorkflowProgress($request);
        
        // Get approval history
        $approvalHistory = $this->workflowService->getApprovalHistory($request);
        
        // Get current step for this user
        $currentStep = $request->getCurrentStep();
        
        return view('approvals.show', compact('request', 'progress', 'approvalHistory', 'currentStep'));
    }

    /**
     * Process approval for a request
     */
    public function approve(Request $request, WorkflowRequest $workflowRequest)
    {
        $user = $request->user();
        
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        try {
            $this->workflowService->processApproval(
                $workflowRequest,
                $user,
                'approve',
                $request->comments
            );

            return redirect()->route('approvals.index')
                ->with('success', 'Request approved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process rejection for a request
     */
    public function reject(Request $request, WorkflowRequest $workflowRequest)
    {
        $user = $request->user();
        
        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        try {
            $this->workflowService->processApproval(
                $workflowRequest,
                $user,
                'reject',
                $request->comments
            );

            return redirect()->route('approvals.index')
                ->with('success', 'Request rejected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get approval statistics for the authenticated user
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        
        // Get approval statistics
        $stats = [
            'pending_count' => $this->workflowService->getPendingApprovalsForUser($user)->count(),
            'approved_today' => RequestApproval::where('approver_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => RequestApproval::where('approver_id', $user->id)
                ->where('status', 'rejected')
                ->whereDate('rejected_at', today())
                ->count(),
            'total_approved' => RequestApproval::where('approver_id', $user->id)
                ->where('status', 'approved')
                ->count(),
            'total_rejected' => RequestApproval::where('approver_id', $user->id)
                ->where('status', 'rejected')
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk approve multiple requests
     */
    public function bulkApprove(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:requests,id',
            'comments' => 'nullable|string|max:1000',
        ]);

        $successCount = 0;
        $errors = [];

        foreach ($request->request_ids as $requestId) {
            try {
                $workflowRequest = WorkflowRequest::findOrFail($requestId);
                
                $this->workflowService->processApproval(
                    $workflowRequest,
                    $user,
                    'approve',
                    $request->comments
                );
                
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Request #{$requestId}: {$e->getMessage()}";
            }
        }

        $message = "{$successCount} requests approved successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->route('approvals.index')
            ->with($successCount > 0 ? 'success' : 'error', $message);
    }
}
