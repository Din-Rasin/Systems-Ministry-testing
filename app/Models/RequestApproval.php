<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestApproval extends Model
{
	use HasFactory;

	protected $fillable = [
		'request_id',
		'workflow_step_id',
		'approver_id',
		'decision',
		'comment',
		'decided_at',
	];

	public function request(): BelongsTo
	{
		return $this->belongsTo(Request::class);
	}

	public function step(): BelongsTo
	{
		return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
	}

	public function approver(): BelongsTo
	{
		return $this->belongsTo(User::class, 'approver_id');
	}
}