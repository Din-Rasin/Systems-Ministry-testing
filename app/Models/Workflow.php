<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
	use HasFactory;

	protected $fillable = ['name', 'request_type'];

	public function steps(): HasMany
	{
		return $this->hasMany(WorkflowStep::class)->orderBy('order_index');
	}

	public function departments(): BelongsToMany
	{
		return $this->belongsToMany(Department::class)->withTimestamps();
	}
}