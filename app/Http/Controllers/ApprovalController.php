<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Request as WorkflowRequest;
use App\Services\WorkflowEngine;
use App\Exceptions\ApprovalException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    protected $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * Display a listing of pending approvals for the user.
     */
    public function index()
    {
        try {
            $cacheKey = "pending_approvals_" . Auth::id();
            $approvals = Cache::remember($cacheKey, 300, function () {
                return Approval::with(['request.user', 'request.workflow', 'step'])
                    ->where('approver_id', Auth::id())
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();
            });

            return view('approvals.index', compact('approvals'));
        } catch (\Exception $e) {
            Log::error('Approval index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')->with('error', 'Unable to load approvals. Please try again later.');
        }
    }

    /**
     * Show the form for approving or rejecting a request.
     */
    public function show(Approval $approval)
    {
        try {
            // Check if user is authorized to view this approval
            if ($approval->approver_id !== Auth::id()) {
                abort(403);
            }

            $cacheKey = "approval_show_" . $approval->id;
            $cachedApproval = Cache::remember($cacheKey, 300, function () use ($approval) {
                $approval->load(['request.user', 'request.workflow', 'request.leaveRequest', 'request.missionRequest', 'step']);
                return $approval;
            });

            return view('approvals.workflow-interface', compact('cachedApproval'));
        } catch (\Exception $e) {
            Log::error('Approval show error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('approvals.index')->with('error', 'Unable to load approval. Please try again later.');
        }
    }

    /**
     * Process an approval decision.
     */
    public function store(Approval $approval, Request $request)
    {
        try {
            // Check if user is authorized to approve this request
            if ($approval->approver_id !== Auth::id()) {
                abort(403);
            }

            // Validate the decision
            $validatedData = $request->validate([
                'decision' => 'required|in:approved,rejected',
                'comments' => 'nullable|string|max:1000',
            ]);

            // Process the approval
            $result = $this->workflowEngine->processApproval(
                $approval,
                $validatedData['decision'],
                $validatedData['comments'] ?? null
            );

            // Clear relevant caches
            Cache::forget("pending_approvals_" . Auth::id());
            Cache::forget("approval_show_" . $approval->id);
            Cache::forget("request_show_" . $approval->request_id);
            Cache::forget("requests_index_" . $approval->request->user_id . "_");

            return redirect()->route('approvals.index')
                ->with('success', 'Approval processed successfully.');
        } catch (ApprovalException $e) {
            Log::error('Approval exception: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'input' => $request->input(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Approval Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Approval processing error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'input' => $request->input(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }

    /**
     * Approve a request.
     */
    public function approve(Approval $approval, Request $request)
    {
        try {
            // Check if user is authorized to approve this request
            if ($approval->approver_id !== Auth::id()) {
                abort(403);
            }

            // Validate the decision
            $validatedData = $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            // Process the approval
            $result = $this->workflowEngine->processApproval(
                $approval,
                'approved',
                $validatedData['comments'] ?? null
            );

            // Clear relevant caches
            Cache::forget("pending_approvals_" . Auth::id());
            Cache::forget("approval_show_" . $approval->id);
            Cache::forget("request_show_" . $approval->request_id);
            Cache::forget("requests_index_" . $approval->request->user_id . "_");

            return redirect()->route('approvals.index')
                ->with('success', 'Request approved successfully.');
        } catch (ApprovalException $e) {
            Log::error('Approval exception: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'input' => $request->input(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Approval Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Approval processing error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'input' => $request->input(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a request.
     */
    public function reject(Approval $approval, Request $request)
    {
        try {
            // Check if user is authorized to approve this request
            if ($approval->approver_id !== Auth::id()) {
                abort(403);
            }

            // Validate the decision
            $validatedData = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);

            // Process the approval
            $result = $this->workflowEngine->processApproval(
                $approval,
                'rejected',
                $validatedData['comments']
            );

            // Clear relevant caches
            Cache::forget("pending_approvals_" . Auth::id());
            Cache::forget("approval_show_" . $approval->id);
            Cache::forget("request_show_" . $approval->request_id);
            Cache::forget("requests_index_" . $approval->request->user_id . "_");

            return redirect()->route('approvals.index')
                ->with('success', 'Request rejected successfully.');
        } catch (ApprovalException $e) {
            Log::error('Approval exception: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'input' => $request->input(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Approval Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Approval processing error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'approval_id' => $approval->id,
                'input' => $request->input(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of all approvals (for admins).
     */
    public function all(Request $request)
    {
        try {
            $this->authorize('viewAny', Approval::class);

            // Build the query
            $query = Approval::with(['request.user', 'approver', 'step.role']);

            // Apply user-based filtering
            $query->when(!Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Regular users can only see their own approvals
                return $query->where('approver_id', Auth::id());
            });

            $query->when(Auth::user()->hasAnyRole(['System Administrator', 'Department Administrator']), function ($query) {
                // Admins can see all approvals in their department
                return $query->whereHas('request.user', function ($subQuery) {
                    $subQuery->where('department_id', Auth::user()->department_id);
                });
            });

            // Apply filters from request
            if ($request->has('status') && $request->input('status') !== '') {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('request_type') && $request->input('request_type') !== '') {
                $query->whereHas('request', function ($subQuery) use ($request) {
                    $subQuery->where('type', $request->input('request_type'));
                });
            }

            if ($request->has('date_from') && $request->input('date_from') !== '') {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to') && $request->input('date_to') !== '') {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Get paginated results
            $approvals = $query->orderBy('created_at', 'desc')->paginate(15);

            return view('approvals.all', compact('approvals'));
        } catch (\Exception $e) {
            Log::error('All approvals error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')->with('error', 'Unable to load approvals. Please try again later.');
        }
    }
}
