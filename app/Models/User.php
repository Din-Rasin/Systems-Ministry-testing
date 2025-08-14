<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'employee_id',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Departments that this user belongs to
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Roles that this user has
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('department_id')
            ->withTimestamps();
    }

    /**
     * Requests submitted by this user
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Request approvals handled by this user
     */
    public function requestApprovals(): HasMany
    {
        return $this->hasMany(RequestApproval::class, 'approver_id');
    }

    /**
     * Get the user's primary department
     */
    public function primaryDepartment()
    {
        return $this->departments()->wherePivot('is_primary', true)->first();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName, ?int $departmentId = null): bool
    {
        $query = $this->roles()->where('name', $roleName);
        
        if ($departmentId) {
            $query->wherePivot('department_id', $departmentId);
        }
        
        return $query->exists();
    }

    /**
     * Check if user can approve a specific step
     */
    public function canApproveStep(WorkflowStep $step, ?int $departmentId = null): bool
    {
        return $this->hasRole($step->approver_role, $departmentId);
    }

    /**
     * Get pending approvals for this user
     */
    public function pendingApprovals()
    {
        return $this->requestApprovals()
            ->where('status', 'pending')
            ->with(['request', 'workflowStep']);
    }
}
