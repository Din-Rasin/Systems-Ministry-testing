<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'user_id',
        'workflow_id',
        'type',
        'title',
        'description',
        'start_date',
        'end_date',
        'duration_days',
        'reason',
        'destination',
        'estimated_cost',
        'status',
        'comments',
        'attachments',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'attachments' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = static::generateRequestNumber();
            }
            
            if (empty($request->submitted_at)) {
                $request->submitted_at = now();
            }

            // Calculate duration in days
            if ($request->start_date && $request->end_date) {
                $request->duration_days = $request->start_date->diffInDays($request->end_date) + 1;
            }
        });
    }

    /**
     * User who submitted the request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Workflow for this request
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Approvals for this request
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(RequestApproval::class)->orderBy('sequence');
    }

    /**
     * Generate unique request number
     */
    public static function generateRequestNumber(): string
    {
        do {
            $number = strtoupper(Str::random(2)) . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Get current approval step
     */
    public function getCurrentStep(): ?WorkflowStep
    {
        return $this->workflow->getCurrentStepForRequest($this);
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Approve the request
     */
    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the request
     */
    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    /**
     * Cancel the request
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get approval progress percentage
     */
    public function getApprovalProgress(): float
    {
        $totalSteps = $this->workflow->activeSteps()->count();
        $approvedSteps = $this->approvals()->where('status', 'approved')->count();

        return $totalSteps > 0 ? ($approvedSteps / $totalSteps) * 100 : 0;
    }

    /**
     * Check if user can approve current step
     */
    public function canBeApprovedBy(User $user): bool
    {
        $currentStep = $this->getCurrentStep();
        
        if (!$currentStep || !$this->isPending()) {
            return false;
        }

        return $currentStep->canBeApprovedBy($user, $this->user->primaryDepartment()?->id);
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatus(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
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
            'cancelled' => 'secondary',
            default => 'primary',
        };
    }
}
