<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\Department;
use App\Models\Role;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $itDepartment = Department::where('name', 'IT Department')->first();
        $salesDepartment = Department::where('name', 'Sales Department')->first();
        
        $teamLeaderRole = Role::where('name', 'Team Leader')->first();
        $hrManagerRole = Role::where('name', 'HR Manager')->first();
        $ceoRole = Role::where('name', 'CEO')->first();
        $cfoRole = Role::where('name', 'CFO')->first();

        // IT Department Leave Workflow
        $itLeaveWorkflow = Workflow::create([
            'name' => 'IT Department Leave Approval',
            'department_id' => $itDepartment->id,
            'type' => 'leave',
            'description' => 'Leave approval workflow for IT Department',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $itLeaveWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $itLeaveWorkflow->id,
            'step_number' => 2,
            'role_id' => $hrManagerRole->id,
            'description' => 'HR Manager Approval',
            'is_active' => true,
        ]);

        // IT Department Mission Workflow
        $itMissionWorkflow = Workflow::create([
            'name' => 'IT Department Mission Approval',
            'department_id' => $itDepartment->id,
            'type' => 'mission',
            'description' => 'Mission approval workflow for IT Department',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $itMissionWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $itMissionWorkflow->id,
            'step_number' => 2,
            'role_id' => $ceoRole->id,
            'description' => 'CEO Approval',
            'is_active' => true,
        ]);

        // Sales Department Leave Workflow
        $salesLeaveWorkflow = Workflow::create([
            'name' => 'Sales Department Leave Approval',
            'department_id' => $salesDepartment->id,
            'type' => 'leave',
            'description' => 'Leave approval workflow for Sales Department',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesLeaveWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesLeaveWorkflow->id,
            'step_number' => 2,
            'role_id' => $cfoRole->id,
            'description' => 'CFO Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesLeaveWorkflow->id,
            'step_number' => 3,
            'role_id' => $hrManagerRole->id,
            'description' => 'HR Manager Approval',
            'is_active' => true,
        ]);

        // Sales Department Mission Workflow
        $salesMissionWorkflow = Workflow::create([
            'name' => 'Sales Department Mission Approval',
            'department_id' => $salesDepartment->id,
            'type' => 'mission',
            'description' => 'Mission approval workflow for Sales Department',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 2,
            'role_id' => $cfoRole->id,
            'description' => 'CFO Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 3,
            'role_id' => $hrManagerRole->id,
            'description' => 'HR Manager Approval',
            'is_active' => true,
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 4,
            'role_id' => $ceoRole->id,
            'description' => 'CEO Approval',
            'is_active' => true,
        ]);
    }
}