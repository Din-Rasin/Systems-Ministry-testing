<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowStep;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createItWorkflows();
        $this->createSalesWorkflows();
        
        $this->command->info('Workflows seeded successfully.');
    }

    private function createItWorkflows(): void
    {
        $itDept = Department::where('code', 'IT')->first();
        
        if (!$itDept) {
            $this->command->error('IT Department not found');
            return;
        }

        // IT Leave Workflow: Team Leader -> HR Manager
        $itLeaveWorkflow = Workflow::updateOrCreate(
            [
                'type' => 'leave',
                'department_id' => $itDept->id,
            ],
            [
                'name' => 'IT Department Leave Approval',
                'description' => 'Leave approval process for IT Department employees',
                'is_active' => true,
            ]
        );

        // IT Leave Steps
        $itLeaveSteps = [
            [
                'name' => 'Team Leader Approval',
                'sequence' => 1,
                'approver_role' => 'team_leader',
                'description' => 'Direct supervisor approval',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'HR Manager Approval',
                'sequence' => 2,
                'approver_role' => 'hr_manager',
                'description' => 'HR department final approval',
                'is_required' => true,
                'is_active' => true,
            ],
        ];

        foreach ($itLeaveSteps as $stepData) {
            WorkflowStep::updateOrCreate(
                [
                    'workflow_id' => $itLeaveWorkflow->id,
                    'sequence' => $stepData['sequence'],
                ],
                array_merge($stepData, ['workflow_id' => $itLeaveWorkflow->id])
            );
        }

        // IT Mission Workflow: Team Leader -> CEO
        $itMissionWorkflow = Workflow::updateOrCreate(
            [
                'type' => 'mission',
                'department_id' => $itDept->id,
            ],
            [
                'name' => 'IT Department Mission Approval',
                'description' => 'Mission approval process for IT Department employees',
                'is_active' => true,
            ]
        );

        // IT Mission Steps
        $itMissionSteps = [
            [
                'name' => 'Team Leader Approval',
                'sequence' => 1,
                'approver_role' => 'team_leader',
                'description' => 'Direct supervisor approval',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'CEO Approval',
                'sequence' => 2,
                'approver_role' => 'ceo',
                'description' => 'Executive approval for mission travel',
                'is_required' => true,
                'is_active' => true,
            ],
        ];

        foreach ($itMissionSteps as $stepData) {
            WorkflowStep::updateOrCreate(
                [
                    'workflow_id' => $itMissionWorkflow->id,
                    'sequence' => $stepData['sequence'],
                ],
                array_merge($stepData, ['workflow_id' => $itMissionWorkflow->id])
            );
        }
    }

    private function createSalesWorkflows(): void
    {
        $salesDept = Department::where('code', 'SALES')->first();
        
        if (!$salesDept) {
            $this->command->error('Sales Department not found');
            return;
        }

        // Sales Leave Workflow: Team Leader -> CFO -> HR Manager
        $salesLeaveWorkflow = Workflow::updateOrCreate(
            [
                'type' => 'leave',
                'department_id' => $salesDept->id,
            ],
            [
                'name' => 'Sales Department Leave Approval',
                'description' => 'Leave approval process for Sales Department employees',
                'is_active' => true,
            ]
        );

        // Sales Leave Steps
        $salesLeaveSteps = [
            [
                'name' => 'Team Leader Approval',
                'sequence' => 1,
                'approver_role' => 'team_leader',
                'description' => 'Direct supervisor approval',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'CFO Approval',
                'sequence' => 2,
                'approver_role' => 'cfo',
                'description' => 'Financial approval for sales team leave',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'HR Manager Approval',
                'sequence' => 3,
                'approver_role' => 'hr_manager',
                'description' => 'HR department final approval',
                'is_required' => true,
                'is_active' => true,
            ],
        ];

        foreach ($salesLeaveSteps as $stepData) {
            WorkflowStep::updateOrCreate(
                [
                    'workflow_id' => $salesLeaveWorkflow->id,
                    'sequence' => $stepData['sequence'],
                ],
                array_merge($stepData, ['workflow_id' => $salesLeaveWorkflow->id])
            );
        }

        // Sales Mission Workflow: Team Leader -> CFO -> HR Manager -> CEO
        $salesMissionWorkflow = Workflow::updateOrCreate(
            [
                'type' => 'mission',
                'department_id' => $salesDept->id,
            ],
            [
                'name' => 'Sales Department Mission Approval',
                'description' => 'Mission approval process for Sales Department employees',
                'is_active' => true,
            ]
        );

        // Sales Mission Steps
        $salesMissionSteps = [
            [
                'name' => 'Team Leader Approval',
                'sequence' => 1,
                'approver_role' => 'team_leader',
                'description' => 'Direct supervisor approval',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'CFO Approval',
                'sequence' => 2,
                'approver_role' => 'cfo',
                'description' => 'Financial approval for mission costs',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'HR Manager Approval',
                'sequence' => 3,
                'approver_role' => 'hr_manager',
                'description' => 'HR compliance and policy approval',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'CEO Approval',
                'sequence' => 4,
                'approver_role' => 'ceo',
                'description' => 'Executive final approval for sales mission',
                'is_required' => true,
                'is_active' => true,
            ],
        ];

        foreach ($salesMissionSteps as $stepData) {
            WorkflowStep::updateOrCreate(
                [
                    'workflow_id' => $salesMissionWorkflow->id,
                    'sequence' => $stepData['sequence'],
                ],
                array_merge($stepData, ['workflow_id' => $salesMissionWorkflow->id])
            );
        }
    }
}
