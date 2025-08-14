<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
	use HasFactory;

	protected $fillable = [
		'workflow_id',
		'order_index',
		'approver_role_slug',
	];

	public function workflow(): BelongsTo
	{
		return $this->belongsTo(Workflow::class);
	}
}