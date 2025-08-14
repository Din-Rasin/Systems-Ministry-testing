<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use App\Models\RequestApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApprovalController extends Controller
{
	public function inbox(Request $httpRequest): View
	{
		$roleSlugs = $this->getUserRoleSlugs();

		$pendingApprovals = RequestApproval::with(['request.user', 'step', 'request.department'])
			->where('decision', 'pending')
			->whereHas('request', function ($q) {
				$q->where('status', 'pending');
			})
			->whereHas('step', function ($q) use ($roleSlugs) {
				$q->whereIn('approver_role_slug', $roleSlugs);
			})
			->orderBy('created_at')
			->paginate(15);

		return view('approvals.inbox', compact('pendingApprovals'));
	}

	public function decide(Request $httpRequest, RequestApproval $approval): RedirectResponse
	{
		$data = $httpRequest->validate([
			'decision' => 'required|in:approved,rejected',
			'comment' => 'nullable|string|max:2000',
		]);

		// Ensure this approval is still pending and is the next current step
		$request = $approval->request()->with('approvals.step')->first();
		if ($approval->decision !== 'pending' || $request->status !== 'pending') {
			return back()->with('status', 'Request no longer actionable.');
		}
		$firstPending = $request->approvals()->where('decision', 'pending')->orderBy('workflow_step_id')->first();
		if ($firstPending && $firstPending->id !== $approval->id) {
			return back()->with('status', 'You can only act on the current step.');
		}

		$approval->update([
			'approver_id' => Auth::id(),
			'decision' => $data['decision'],
			'comment' => $data['comment'] ?? null,
			'decided_at' => now(),
		]);

		if ($data['decision'] === 'rejected') {
			$request->update(['status' => 'rejected']);
			return back()->with('status', 'Request rejected.');
		}

		$nextPending = $request->approvals()
			->where('decision', 'pending')
			->orderBy('workflow_step_id')
			->first();

		if (!$nextPending) {
			$request->update(['status' => 'approved']);
			return back()->with('status', 'Request fully approved.');
		}

		return back()->with('status', 'Step approved. Next approver notified.');
	}

	private function getUserRoleSlugs(): array
	{
		$authUser = Auth::user();
		if (!$authUser) {
			return [];
		}
		if (method_exists($authUser, 'hasRole') && $authUser->hasRole('admin')) {
			return ['team_leader', 'hr_manager', 'ceo', 'cfo'];
		}
		return ['team_leader'];
	}
}