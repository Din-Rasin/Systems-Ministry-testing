<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Request as UserRequest;
use App\Models\RequestApproval;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RequestController extends Controller
{
	public function index(Request $httpRequest): View
	{
		$requests = UserRequest::with('approvals.step')
			->where('user_id', Auth::id())
			->orderByDesc('created_at')
			->paginate(15);

		return view('requests.index', compact('requests'));
	}

	public function create(Request $httpRequest): View
	{
		$departments = Department::orderBy('name')->get();
		return view('requests.create', compact('departments'));
	}

	public function store(Request $httpRequest): RedirectResponse
	{
		$data = $httpRequest->validate([
			'type' => 'required|in:leave,mission',
			'department_id' => 'required|exists:departments,id',
			'start_date' => 'nullable|date',
			'end_date' => 'nullable|date|after_or_equal:start_date',
			'destination' => 'nullable|string|max:255',
			'reason' => 'nullable|string|max:2000',
		]);

		$departmentId = (int) $data['department_id'];

		$workflow = Workflow::where('request_type', $data['type'])
			->whereHas('departments', function ($q) use ($departmentId) {
				$q->where('departments.id', $departmentId);
			})
			->with('steps')
			->firstOrFail();

		$request = UserRequest::create([
			'user_id' => Auth::id(),
			'department_id' => $departmentId,
			'type' => $data['type'],
			'start_date' => $data['start_date'] ?? null,
			'end_date' => $data['end_date'] ?? null,
			'destination' => $data['destination'] ?? null,
			'reason' => $data['reason'] ?? null,
			'status' => 'pending',
			'workflow_id' => $workflow->id,
			'current_step_index' => 0,
		]);

		foreach ($workflow->steps as $step) {
			RequestApproval::create([
				'request_id' => $request->id,
				'workflow_step_id' => $step->id,
				'decision' => 'pending',
			]);
		}

		return redirect()->route('requests.index')->with('status', 'Request submitted.');
	}
}