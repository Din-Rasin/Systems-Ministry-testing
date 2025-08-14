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
        'status',
        'comments',
        'approved_at',
        'rejected_at',
        'sequence',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($approval) {
            if ($approval->isDirty('status')) {
                if ($approval->status === 'approved') {
                    $approval->approved_at = now();
                    $approval->rejected_at = null;
                } elseif ($approval->status === 'rejected') {
                    $approval->rejected_at = now();
                    $approval->approved_at = null;
                }
            }
        });
    }

    /**
     * Request this approval belongs to
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * Workflow step this approval is for
     */
    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    /**
     * User who approved/rejected this step
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Check if this approval is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this approval is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if this approval is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve this step
     */
    public function approve(User $approver, ?string $comments = null): void
    {
        $this->update([
            'status' => 'approved',
            'approver_id' => $approver->id,
            'comments' => $comments,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject this step
     */
    public function reject(User $approver, ?string $comments = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'comments' => $comments,
            'rejected_at' => now(),
        ]);
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatus(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'primary',
        };
    }

    /**
     * Get the action date (approved_at or rejected_at)
     */
    public function getActionDate(): ?string
    {
        if ($this->isApproved()) {
            return $this->approved_at?->format('M d, Y H:i');
        }
        
        if ($this->isRejected()) {
            return $this->rejected_at?->format('M d, Y H:i');
        }

        return null;
    }
}
