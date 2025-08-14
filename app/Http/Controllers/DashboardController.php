<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WorkflowService;
use App\Models\Request as WorkflowRequest;

class DashboardController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->middleware('auth');
        $this->workflowService = $workflowService;
    }

    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get workflow statistics
        $stats = $this->workflowService->getWorkflowStatistics($user);
        
        // Get recent requests by the user
        $recentRequests = $user->requests()
            ->with(['workflow.department'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get pending approvals for the user
        $pendingApprovals = $this->workflowService->getPendingApprovalsForUser($user);
        
        return view('dashboard.index', compact('stats', 'recentRequests', 'pendingApprovals'));
    }

    /**
     * Get dashboard data as JSON for AJAX requests
     */
    public function data(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'stats' => $this->workflowService->getWorkflowStatistics($user),
            'pending_approvals_count' => $this->workflowService->getPendingApprovalsForUser($user)->count(),
        ]);
    }
}
