<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Users that belong to this department
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Department manager
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Workflows for this department
     */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    /**
     * Get workflow for a specific type (leave or mission)
     */
    public function getWorkflowForType(string $type): ?Workflow
    {
        return $this->workflows()
            ->where('type', $type)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get users with specific role in this department
     */
    public function getUsersWithRole(string $roleName)
    {
        return $this->users()
            ->whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName)
                    ->wherePivot('department_id', $this->id);
            });
    }

    /**
     * Get team leaders for this department
     */
    public function teamLeaders()
    {
        return $this->getUsersWithRole('team_leader');
    }
}
