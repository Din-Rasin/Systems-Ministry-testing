<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'sequence',
        'approver_role',
        'description',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Workflow this step belongs to
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Request approvals for this step
     */
    public function requestApprovals(): HasMany
    {
        return $this->hasMany(RequestApproval::class);
    }

    /**
     * Get users who can approve this step in a specific department
     */
    public function getApprovers(?int $departmentId = null)
    {
        $query = User::whereHas('roles', function ($roleQuery) use ($departmentId) {
            $roleQuery->where('name', $this->approver_role);
            
            if ($departmentId) {
                $roleQuery->wherePivot('department_id', $departmentId);
            }
        });

        return $query->where('is_active', true)->get();
    }

    /**
     * Check if this step is approved for a specific request
     */
    public function isApprovedForRequest(Request $request): bool
    {
        return $this->requestApprovals()
            ->where('request_id', $request->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Check if this step is rejected for a specific request
     */
    public function isRejectedForRequest(Request $request): bool
    {
        return $this->requestApprovals()
            ->where('request_id', $request->id)
            ->where('status', 'rejected')
            ->exists();
    }

    /**
     * Get the approval record for a specific request
     */
    public function getApprovalForRequest(Request $request): ?RequestApproval
    {
        return $this->requestApprovals()
            ->where('request_id', $request->id)
            ->first();
    }

    /**
     * Check if a user can approve this step
     */
    public function canBeApprovedBy(User $user, ?int $departmentId = null): bool
    {
        return $user->hasRole($this->approver_role, $departmentId);
    }
}
