<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Request as UserRequest;
use App\Models\RequestApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowApprovalTest extends TestCase
{
	use RefreshDatabase;

	public function test_full_approval_marks_request_approved(): void
	{
		$this->seed();
		$user = User::where('email', 'user@example.com')->first();
		$this->be($user);
		$it = Department::where('name', 'IT')->first();
		$this->post('/requests', [
			'type' => 'leave',
			'department_id' => $it->id,
			'start_date' => now()->toDateString(),
			'end_date' => now()->addDay()->toDateString(),
		]);

		$request = UserRequest::first();
		$this->assertNotNull($request);

		// Act as admin to approve all
		$admin = User::where('email', 'admin@example.com')->first();
		$this->be($admin);

		$approvals = RequestApproval::where('request_id', $request->id)->orderBy('workflow_step_id')->get();
		foreach ($approvals as $ap) {
			$this->post(route('approvals.decide', $ap), ['decision' => 'approved'])->assertSessionHas('status');
		}

		$request->refresh();
		$this->assertEquals('approved', $request->status);
	}
}