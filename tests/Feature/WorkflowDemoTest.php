<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Request as UserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowDemoTest extends TestCase
{
	use RefreshDatabase;

	public function test_can_seed_and_create_request(): void
	{
		$this->seed();
		$user = User::where('email', 'user@example.com')->first();
		$this->be($user);

		$it = Department::where('name', 'IT')->first();
		$response = $this->post('/requests', [
			'type' => 'leave',
			'department_id' => $it->id,
			'start_date' => now()->toDateString(),
			'end_date' => now()->addDay()->toDateString(),
			'reason' => 'Vacation',
		]);
		$response->assertRedirect('/requests');

		$this->assertDatabaseHas('requests', [
			'user_id' => $user->id,
			'department_id' => $it->id,
			'type' => 'leave',
			'status' => 'pending',
		]);
	}
}