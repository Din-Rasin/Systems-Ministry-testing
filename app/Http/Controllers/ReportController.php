<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Request as WorkflowRequest;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        return view('reports.dashboard');
    }

    /**
     * Display request statistics report.
     */
    public function requestStatistics(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get request statistics by status
        $requestStats = WorkflowRequest::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
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
            ->groupBy('status')
            ->get();

        // Get request statistics by type
        $typeStats = WorkflowRequest::select('type', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
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
            ->groupBy('type')
            ->get();

        // Get request statistics by department (for admins only)
        $departmentStats = [];
        if (Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator'])) {
            $departmentStats = WorkflowRequest::select('departments.name as department', DB::raw('count(*) as count'))
                ->join('users', 'requests.user_id', '=', 'users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('departments', 'user_roles.department_id', '=', 'departments.id')
                ->whereBetween('requests.created_at', [$startDate, $endDate])
                ->when(Auth::user()->hasRole('Department Administrator'), function ($query) {
                    // Department admins can only see their department
                    return $query->where('departments.id', Auth::user()->department_id);
                })
                ->groupBy('departments.name')
                ->get();
        }

        return view('reports.request-statistics', compact('requestStats', 'typeStats', 'departmentStats', 'startDate', 'endDate'));
    }

    /**
     * Display approval statistics report.
     */
    public function approvalStatistics(Request $request)
    {
        $this->authorize('viewAny', Approval::class);

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get approval statistics by status
        $approvalStats = Approval::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when(!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Regular users can only see their own approvals
                return $query->where('approver_id', Auth::id());
            })
            ->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Admins can see all approvals in their department
                return $query->whereHas('request.user', function ($subQuery) {
                    $subQuery->where('department_id', Auth::user()->department_id);
                });
            })
            ->groupBy('status')
            ->get();

        // Get approval statistics by user (for admins only)
        $userStats = [];
        if (Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator'])) {
            $userStats = Approval::select('users.name as user', DB::raw('count(*) as count'))
                ->join('users', 'approvals.approver_id', '=', 'users.id')
                ->whereBetween('approvals.created_at', [$startDate, $endDate])
                ->when(Auth::user()->hasRole('Department Administrator'), function ($query) {
                    // Department admins can only see their department
                    return $query->whereHas('request.user', function ($subQuery) {
                        $subQuery->where('department_id', Auth::user()->department_id);
                    });
                })
                ->groupBy('users.name')
                ->get();
        }

        return view('reports.approval-statistics', compact('approvalStats', 'userStats', 'startDate', 'endDate'));
    }

    /**
     * Display workflow performance report.
     */
    public function workflowPerformance(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get average processing time for requests
        $avgProcessingTime = WorkflowRequest::select(
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, decision_at)) as avg_hours')
            )
            ->whereNotNull('decision_at')
            ->whereBetween('created_at', [$startDate, $endDate])
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
            ->first();

        // Get request completion rates by workflow
        $completionRates = WorkflowRequest::select(
                'workflows.name as workflow',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
            )
            ->join('workflows', 'requests.workflow_id', '=', 'workflows.id')
            ->whereBetween('requests.created_at', [$startDate, $endDate])
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
            ->groupBy('workflows.name')
            ->get();

        return view('reports.workflow-performance', compact('avgProcessingTime', 'completionRates', 'startDate', 'endDate'));
    }

    /**
     * Export request data as CSV.
     */
    public function exportRequests(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $requests = WorkflowRequest::with(['user', 'workflow'])
            ->whereBetween('created_at', [$startDate, $endDate])
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
            ->get();

        $filename = "requests_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add CSV headers
        fputcsv($handle, ['ID', 'User', 'Type', 'Status', 'Workflow', 'Submitted At', 'Decision At']);

        // Add data rows
        foreach ($requests as $request) {
            fputcsv($handle, [
                $request->id,
                $request->user->name,
                $request->type,
                $request->status,
                $request->workflow->name,
                $request->submitted_at,
                $request->decision_at,
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Export approval data as CSV.
     */
    public function exportApprovals(Request $request)
    {
        $this->authorize('viewAny', Approval::class);

        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $approvals = Approval::with(['request.user', 'approver'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when(!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Regular users can only see their own approvals
                return $query->where('approver_id', Auth::id());
            })
            ->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Admins can see all approvals in their department
                return $query->whereHas('request.user', function ($subQuery) {
                    $subQuery->where('department_id', Auth::user()->department_id);
                });
            })
            ->get();

        $filename = "approvals_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add CSV headers
        fputcsv($handle, ['ID', 'Request ID', 'Requester', 'Approver', 'Status', 'Comments', 'Approved At']);

        // Add data rows
        foreach ($approvals as $approval) {
            fputcsv($handle, [
                $approval->id,
                $approval->request->id,
                $approval->request->user->name,
                $approval->approver->name,
                $approval->status,
                $approval->comments,
                $approval->approved_at,
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Display department performance report.
     */
    public function departmentPerformance(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        // Only system administrators can view department performance
        if (!Auth::user()->hasRole('System Administrator')) {
            abort(403);
        }

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get department performance metrics
        $departmentPerformance = DB::table('departments')
            ->leftJoin('user_roles', 'departments.id', '=', 'user_roles.department_id')
            ->leftJoin('users', 'user_roles.user_id', '=', 'users.id')
            ->leftJoin('requests', function($join) {
                $join->on('users.id', '=', 'requests.user_id')
                    ->where('requests.status', '=', 'approved');
            })
            ->select(
                'departments.name as department',
                DB::raw('COUNT(DISTINCT users.id) as user_count'),
                DB::raw('COUNT(requests.id) as request_count'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, requests.submitted_at, requests.decision_at)) as avg_processing_hours')
            )
            ->whereBetween('requests.created_at', [$startDate, $endDate])
            ->groupBy('departments.name')
            ->get();

        return view('reports.department-performance', compact('departmentPerformance', 'startDate', 'endDate'));
    }

    /**
     * Display user activity report.
     */
    public function userActivity(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get user activity metrics
        $userActivity = DB::table('users')
            ->leftJoin('requests', 'users.id', '=', 'requests.user_id')
            ->leftJoin('approvals', 'users.id', '=', 'approvals.approver_id')
            ->select(
                'users.name as user',
                DB::raw('COUNT(DISTINCT requests.id) as request_count'),
                DB::raw('COUNT(DISTINCT approvals.id) as approval_count')
            )
            ->whereBetween('requests.created_at', [$startDate, $endDate])
            ->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Admins can see all users in their department
                return $query->whereHas('user', function ($subQuery) {
                    $subQuery->where('department_id', Auth::user()->department_id);
                });
            })
            ->groupBy('users.name')
            ->get();

        return view('reports.user-activity', compact('userActivity', 'startDate', 'endDate'));
    }

    /**
     * Display workflow analysis report.
     */
    public function workflowAnalysis(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get workflow analysis metrics
        $workflowAnalysis = DB::table('workflows')
            ->leftJoin('requests', 'workflows.id', '=', 'requests.workflow_id')
            ->select(
                'workflows.name as workflow',
                DB::raw('COUNT(requests.id) as total_requests'),
                DB::raw('SUM(CASE WHEN requests.status = "approved" THEN 1 ELSE 0 END) as approved_requests'),
                DB::raw('SUM(CASE WHEN requests.status = "rejected" THEN 1 ELSE 0 END) as rejected_requests'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, requests.submitted_at, requests.decision_at)) as avg_processing_hours')
            )
            ->whereBetween('requests.created_at', [$startDate, $endDate])
            ->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Admins can see workflows in their department
                return $query->where('workflows.department_id', Auth::user()->department_id);
            })
            ->groupBy('workflows.name')
            ->get();

        return view('reports.workflow-analysis', compact('workflowAnalysis', 'startDate', 'endDate'));
    }

    /**
     * Display system usage statistics report.
     */
    public function systemUsage(Request $request)
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        // Only system administrators can view system usage
        if (!Auth::user()->hasRole('System Administrator')) {
            abort(403);
        }

        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get system usage metrics
        $systemUsage = [
            'total_users' => DB::table('users')->count(),
            'total_departments' => DB::table('departments')->count(),
            'total_workflows' => DB::table('workflows')->count(),
            'total_requests' => DB::table('requests')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_approvals' => DB::table('approvals')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_users' => DB::table('users')->where('is_active', true)->count(),
        ];

        return view('reports.system-usage', compact('systemUsage', 'startDate', 'endDate'));
    }
}
