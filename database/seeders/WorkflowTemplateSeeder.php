<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get department IDs
        $itDepartmentId = DB::table('departments')->where('name', 'IT')->value('id');
        $salesDepartmentId = DB::table('departments')->where('name', 'Sales')->value('id');

        // Get role IDs
        $teamLeaderRoleId = DB::table('roles')->where('name', 'Team Leader')->value('id');
        $hrManagerRoleId = DB::table('roles')->where('name', 'HR Manager')->value('id');
        $cfoRoleId = DB::table('roles')->where('name', 'CFO')->value('id');
        $ceoRoleId = DB::table('roles')->where('name', 'CEO')->value('id');

        // Create IT Department Leave Workflow
        $itLeaveWorkflowId = DB::table('workflows')->insertGetId([
            'name' => 'IT Department Leave Workflow',
            'department_id' => $itDepartmentId,
            'type' => 'leave',
            'description' => 'Leave request workflow for IT Department',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create steps for IT Department Leave Workflow
        DB::table('workflow_steps')->insert([
            [
                'workflow_id' => $itLeaveWorkflowId,
                'step_number' => 1,
                'role_id' => $teamLeaderRoleId,
                'description' => 'Team Leader Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $itLeaveWorkflowId,
                'step_number' => 2,
                'role_id' => $hrManagerRoleId,
                'description' => 'HR Manager Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create Sales Department Leave Workflow
        $salesLeaveWorkflowId = DB::table('workflows')->insertGetId([
            'name' => 'Sales Department Leave Workflow',
            'department_id' => $salesDepartmentId,
            'type' => 'leave',
            'description' => 'Leave request workflow for Sales Department',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create steps for Sales Department Leave Workflow
        DB::table('workflow_steps')->insert([
            [
                'workflow_id' => $salesLeaveWorkflowId,
                'step_number' => 1,
                'role_id' => $teamLeaderRoleId,
                'description' => 'Team Leader Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $salesLeaveWorkflowId,
                'step_number' => 2,
                'role_id' => $cfoRoleId,
                'description' => 'CFO Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $salesLeaveWorkflowId,
                'step_number' => 3,
                'role_id' => $hrManagerRoleId,
                'description' => 'HR Manager Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create IT Department Mission Workflow
        $itMissionWorkflowId = DB::table('workflows')->insertGetId([
            'name' => 'IT Department Mission Workflow',
            'department_id' => $itDepartmentId,
            'type' => 'mission',
            'description' => 'Mission request workflow for IT Department',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create steps for IT Department Mission Workflow
        DB::table('workflow_steps')->insert([
            [
                'workflow_id' => $itMissionWorkflowId,
                'step_number' => 1,
                'role_id' => $teamLeaderRoleId,
                'description' => 'Team Leader Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $itMissionWorkflowId,
                'step_number' => 2,
                'role_id' => $ceoRoleId,
                'description' => 'CEO Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create Sales Department Mission Workflow
        $salesMissionWorkflowId = DB::table('workflows')->insertGetId([
            'name' => 'Sales Department Mission Workflow',
            'department_id' => $salesDepartmentId,
            'type' => 'mission',
            'description' => 'Mission request workflow for Sales Department',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create steps for Sales Department Mission Workflow
        DB::table('workflow_steps')->insert([
            [
                'workflow_id' => $salesMissionWorkflowId,
                'step_number' => 1,
                'role_id' => $teamLeaderRoleId,
                'description' => 'Team Leader Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $salesMissionWorkflowId,
                'step_number' => 2,
                'role_id' => $cfoRoleId,
                'description' => 'CFO Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $salesMissionWorkflowId,
                'step_number' => 3,
                'role_id' => $hrManagerRoleId,
                'description' => 'HR Manager Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $salesMissionWorkflowId,
                'step_number' => 4,
                'role_id' => $ceoRoleId,
                'description' => 'CEO Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
