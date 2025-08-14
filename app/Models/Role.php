<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Users that have this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('department_id')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Add a permission to this role
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Remove a permission from this role
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Get role by name
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Predefined role permissions
     */
    public static function getDefaultPermissions(string $roleName): array
    {
        return match ($roleName) {
            'employee' => ['submit_request', 'view_own_requests'],
            'team_leader' => ['submit_request', 'view_own_requests', 'approve_team_requests'],
            'hr_manager' => ['submit_request', 'view_own_requests', 'approve_leave_requests', 'view_all_requests'],
            'cfo' => ['submit_request', 'view_own_requests', 'approve_mission_requests', 'view_financial_reports'],
            'ceo' => ['submit_request', 'view_own_requests', 'approve_all_requests', 'view_all_requests'],
            'dept_admin' => ['create_workflows', 'manage_department', 'submit_department_requests'],
            'system_admin' => ['manage_users', 'manage_roles', 'manage_departments', 'manage_workflows', 'view_all_requests'],
            default => [],
        };
    }
}
