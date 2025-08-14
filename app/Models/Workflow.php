<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'department_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Department this workflow belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Steps in this workflow
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('sequence');
    }

    /**
     * Active steps in this workflow
     */
    public function activeSteps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)
            ->where('is_active', true)
            ->orderBy('sequence');
    }

    /**
     * Requests using this workflow
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Get the first step of the workflow
     */
    public function getFirstStep(): ?WorkflowStep
    {
        return $this->activeSteps()->first();
    }

    /**
     * Get the next step after a given step
     */
    public function getNextStep(WorkflowStep $currentStep): ?WorkflowStep
    {
        return $this->activeSteps()
            ->where('sequence', '>', $currentStep->sequence)
            ->first();
    }

    /**
     * Get the step by sequence number
     */
    public function getStepBySequence(int $sequence): ?WorkflowStep
    {
        return $this->activeSteps()
            ->where('sequence', $sequence)
            ->first();
    }

    /**
     * Check if workflow is complete for a request
     */
    public function isCompleteForRequest(Request $request): bool
    {
        $totalSteps = $this->activeSteps()->count();
        $approvedSteps = $request->approvals()
            ->where('status', 'approved')
            ->count();

        return $totalSteps === $approvedSteps;
    }

    /**
     * Get current step for a request
     */
    public function getCurrentStepForRequest(Request $request): ?WorkflowStep
    {
        // Get the next unapproved step
        $approvedSteps = $request->approvals()
            ->where('status', 'approved')
            ->with('workflowStep')
            ->get()
            ->pluck('workflowStep.sequence')
            ->toArray();

        return $this->activeSteps()
            ->whereNotIn('sequence', $approvedSteps)
            ->first();
    }
}
