<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\Department;
use App\Models\Role;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $itDepartment = Department::where('name', 'IT Department')->first();
        $salesDepartment = Department::where('name', 'Sales Department')->first();

        // Get roles
        $teamLeaderRole = Role::where('name', 'Team Leader')->first();
        $hrManagerRole = Role::where('name', 'HR Manager')->first();
        $cfoRole = Role::where('name', 'CFO')->first();
        $ceoRole = Role::where('name', 'CEO')->first();

        // Create IT Department Leave Workflow
        $itLeaveWorkflow = Workflow::create([
            'name' => 'IT Department Leave Workflow',
            'department_id' => $itDepartment->id,
            'type' => 'leave',
            'description' => 'Leave request workflow for IT Department',
            'is_active' => true,
        ]);

        // Create steps for IT Leave Workflow
        WorkflowStep::create([
            'workflow_id' => $itLeaveWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $itLeaveWorkflow->id,
            'step_number' => 2,
            'role_id' => $hrManagerRole->id,
            'description' => 'HR Manager Approval',
        ]);

        // Create IT Department Mission Workflow
        $itMissionWorkflow = Workflow::create([
            'name' => 'IT Department Mission Workflow',
            'department_id' => $itDepartment->id,
            'type' => 'mission',
            'description' => 'Mission request workflow for IT Department',
            'is_active' => true,
        ]);

        // Create steps for IT Mission Workflow
        WorkflowStep::create([
            'workflow_id' => $itMissionWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $itMissionWorkflow->id,
            'step_number' => 2,
            'role_id' => $ceoRole->id,
            'description' => 'CEO Approval',
        ]);

        // Create Sales Department Leave Workflow
        $salesLeaveWorkflow = Workflow::create([
            'name' => 'Sales Department Leave Workflow',
            'department_id' => $salesDepartment->id,
            'type' => 'leave',
            'description' => 'Leave request workflow for Sales Department',
            'is_active' => true,
        ]);

        // Create steps for Sales Leave Workflow
        WorkflowStep::create([
            'workflow_id' => $salesLeaveWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesLeaveWorkflow->id,
            'step_number' => 2,
            'role_id' => $cfoRole->id,
            'description' => 'CFO Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesLeaveWorkflow->id,
            'step_number' => 3,
            'role_id' => $hrManagerRole->id,
            'description' => 'HR Manager Approval',
        ]);

        // Create Sales Department Mission Workflow
        $salesMissionWorkflow = Workflow::create([
            'name' => 'Sales Department Mission Workflow',
            'department_id' => $salesDepartment->id,
            'type' => 'mission',
            'description' => 'Mission request workflow for Sales Department',
            'is_active' => true,
        ]);

        // Create steps for Sales Mission Workflow
        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 1,
            'role_id' => $teamLeaderRole->id,
            'description' => 'Team Leader Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 2,
            'role_id' => $cfoRole->id,
            'description' => 'CFO Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 3,
            'role_id' => $hrManagerRole->id,
            'description' => 'HR Manager Approval',
        ]);

        WorkflowStep::create([
            'workflow_id' => $salesMissionWorkflow->id,
            'step_number' => 4,
            'role_id' => $ceoRole->id,
            'description' => 'CEO Approval',
        ]);
    }
}
