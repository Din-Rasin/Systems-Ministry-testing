<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
	public function run(): void
	{
		$it = Department::firstOrCreate(['name' => 'IT']);
		$sales = Department::firstOrCreate(['name' => 'Sales']);

		$admin = User::firstOrCreate(
			['email' => 'admin@example.com'],
			['name' => 'Admin', 'password' => Hash::make('password')]
		);
		$admin->departments()->syncWithoutDetaching([$it->id, $sales->id]);

		$user = User::firstOrCreate(
			['email' => 'user@example.com'],
			['name' => 'User', 'password' => Hash::make('password')]
		);
		$user->departments()->syncWithoutDetaching([$it->id]);

		$approver = User::firstOrCreate(
			['email' => 'approver@example.com'],
			['name' => 'Approver', 'password' => Hash::make('password')]
		);
		$approver->departments()->syncWithoutDetaching([$it->id, $sales->id]);

		// Roles
		$roles = collect([
			['name' => 'Team Leader', 'slug' => 'team_leader', 'scope' => 'department'],
			['name' => 'HR Manager', 'slug' => 'hr_manager', 'scope' => 'global'],
			['name' => 'CEO', 'slug' => 'ceo', 'scope' => 'global'],
			['name' => 'CFO', 'slug' => 'cfo', 'scope' => 'global'],
			['name' => 'Admin', 'slug' => 'admin', 'scope' => 'global'],
		]);
		$roles->each(function ($r) {
			Role::firstOrCreate(['slug' => $r['slug']], ['name' => $r['name'], 'scope' => $r['scope']]);
		});

		$teamLeader = Role::where('slug', 'team_leader')->first();
		$hr = Role::where('slug', 'hr_manager')->first();
		$ceo = Role::where('slug', 'ceo')->first();
		$cfo = Role::where('slug', 'cfo')->first();
		$adminRole = Role::where('slug', 'admin')->first();

		$admin->roles()->syncWithoutDetaching([$adminRole->id]);
		$approver->roles()->syncWithoutDetaching([$hr->id, $ceo->id, $cfo->id]);
		// Department-scoped role
		$approver->roles()->syncWithoutDetaching([$teamLeader->id => ['department_id' => $it->id]]);
		$user->roles()->syncWithoutDetaching([$teamLeader->id => ['department_id' => $it->id]]);

		// IT Department Workflows
		$itLeave = Workflow::firstOrCreate(['name' => 'IT Leave', 'request_type' => 'leave']);
		$itLeave->steps()->delete();
		$this->createSteps($itLeave, ['team_leader', 'hr_manager']);
		$itLeave->departments()->syncWithoutDetaching([$it->id]);

		$itMission = Workflow::firstOrCreate(['name' => 'IT Mission', 'request_type' => 'mission']);
		$itMission->steps()->delete();
		$this->createSteps($itMission, ['team_leader', 'ceo']);
		$itMission->departments()->syncWithoutDetaching([$it->id]);

		// Sales Department Workflows
		$salesLeave = Workflow::firstOrCreate(['name' => 'Sales Leave', 'request_type' => 'leave']);
		$salesLeave->steps()->delete();
		$this->createSteps($salesLeave, ['team_leader', 'cfo', 'hr_manager']);
		$salesLeave->departments()->syncWithoutDetaching([$sales->id]);

		$salesMission = Workflow::firstOrCreate(['name' => 'Sales Mission', 'request_type' => 'mission']);
		$salesMission->steps()->delete();
		$this->createSteps($salesMission, ['team_leader', 'cfo', 'hr_manager', 'ceo']);
		$salesMission->departments()->syncWithoutDetaching([$sales->id]);
	}

	private function createSteps(Workflow $workflow, array $roleSlugs): void
	{
		foreach ($roleSlugs as $i => $slug) {
			WorkflowStep::create([
				'workflow_id' => $workflow->id,
				'order_index' => $i,
				'approver_role_slug' => $slug,
			]);
		}
	}
}
