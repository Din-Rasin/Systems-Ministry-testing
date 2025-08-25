<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkflowRule;
use App\Models\Department;
use Illuminate\Http\Request;

class WorkflowRuleController extends Controller
{
    /**
     * Display a listing of the workflow rules.
     */
    public function index()
    {
        $rules = WorkflowRule::with('department')->orderBy('department_id')->orderBy('request_type')->orderBy('priority_order')->get();
        $departments = Department::all();
        return view('admin.workflow-rules.index', compact('rules', 'departments'));
    }

    /**
     * Show the form for creating a new workflow rule.
     */
    public function create()
    {
        $departments = Department::all();
        return view('admin.workflow-rules.create', compact('departments'));
    }

    /**
     * Store a newly created workflow rule in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'request_type' => 'required|in:LEAVE,MISSION',
            'condition_field' => 'required|string|max:100',
            'condition_operator' => 'required|string|max:20',
            'condition_value' => 'required|string|max:255',
            'action' => 'required|string',
            'priority_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Convert action string to JSON
        $validated['action'] = json_encode([
            'type' => 'route_to_specific_approver',
            'value' => $validated['action']
        ]);

        $validated['is_active'] = $request->has('is_active');

        WorkflowRule::create($validated);

        return redirect()->route('admin.workflow-rules.index')
            ->with('success', 'Workflow rule created successfully.');
    }

    /**
     * Show the form for editing the specified workflow rule.
     */
    public function edit(WorkflowRule $workflowRule)
    {
        $departments = Department::all();

        // Extract action value from array
        $actionValue = '';
        if (!empty($workflowRule->action) && is_array($workflowRule->action) && isset($workflowRule->action['value'])) {
            $actionValue = $workflowRule->action['value'];
        }

        return view('admin.workflow-rules.edit', compact('workflowRule', 'departments', 'actionValue'));
    }

    /**
     * Update the specified workflow rule in storage.
     */
    public function update(Request $request, WorkflowRule $workflowRule)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'request_type' => 'required|in:LEAVE,MISSION',
            'condition_field' => 'required|string|max:100',
            'condition_operator' => 'required|string|max:20',
            'condition_value' => 'required|string|max:255',
            'action' => 'required|string',
            'priority_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Convert action string to JSON
        $validated['action'] = json_encode([
            'type' => 'route_to_specific_approver',
            'value' => $validated['action']
        ]);

        $validated['is_active'] = $request->has('is_active');

        $workflowRule->update($validated);

        return redirect()->route('admin.workflow-rules.index')
            ->with('success', 'Workflow rule updated successfully.');
    }

    /**
     * Remove the specified workflow rule from storage.
     */
    public function destroy(WorkflowRule $workflowRule)
    {
        $workflowRule->delete();

        return redirect()->route('admin.workflow-rules.index')
            ->with('success', 'Workflow rule deleted successfully.');
    }

    /**
     * Toggle the active status of a workflow rule.
     */
    public function toggleActive(WorkflowRule $workflowRule)
    {
        $workflowRule->update(['is_active' => !$workflowRule->is_active]);

        return redirect()->route('admin.workflow-rules.index')
            ->with('success', 'Workflow rule status updated successfully.');
    }
}
