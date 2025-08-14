<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentWorkflowController extends Controller
{
	public function index(): View
	{
		$departments = Department::with(['users', 'id'])->orderBy('name')->get();
		$workflows = Workflow::with('steps')->orderBy('request_type')->orderBy('name')->get();
		return view('admin.workflows.index', compact('departments', 'workflows'));
	}

	public function store(Request $request): RedirectResponse
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'request_type' => 'required|in:leave,mission',
			'steps' => 'required|array|min:1',
			'steps.*.approver_role_slug' => 'required|string',
			'department_ids' => 'required|array|min:1',
			'department_ids.*' => 'exists:departments,id',
		]);

		$workflow = Workflow::create([
			'name' => $data['name'],
			'request_type' => $data['request_type'],
		]);

		foreach ($data['steps'] as $index => $step) {
			WorkflowStep::create([
				'workflow_id' => $workflow->id,
				'order_index' => $index,
				'approver_role_slug' => $step['approver_role_slug'],
			]);
		}

		$workflow->departments()->sync($data['department_ids']);

		return back()->with('status', 'Workflow created and assigned.');
	}
}