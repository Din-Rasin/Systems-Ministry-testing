<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory, Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var list<string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
		];
	}

	public function departments(): BelongsToMany
	{
		return $this->belongsToMany(Department::class)->withTimestamps();
	}

	public function roles(): BelongsToMany
	{
		return $this->belongsToMany(Role::class)->withTimestamps();
	}

	public function rolesForDepartment(?int $departmentId): BelongsToMany
	{
		return $this->belongsToMany(Role::class)->wherePivot('department_id', $departmentId)->withTimestamps();
	}

	public function hasRole(string $slug, ?int $departmentId = null): bool
	{
		$query = $this->roles()->where('slug', $slug);
		if ($departmentId !== null) {
			$query->wherePivot('department_id', $departmentId);
		}
		return $query->exists();
	}
}
