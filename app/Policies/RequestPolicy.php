<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Request as WorkflowRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkflowRequest $request): bool
    {
        // Users can view their own requests
        if ($request->user_id === $user->id) {
            return true;
        }

        // Admins can view all requests in their department
        if ($user->hasAnyRole(['System Administrator', 'Department Administrator'])) {
            return $request->user->department_id === $user->department_id;
        }

        // Approvers can view requests they need to approve
        return $request->approvals()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create requests
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkflowRequest $request): bool
    {
        // Only the request owner can update draft requests
        return $request->user_id === $user->id && $request->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkflowRequest $request): bool
    {
        // Only the request owner can delete draft requests
        return $request->user_id === $user->id && $request->status === 'draft';
    }

    /**
     * Determine whether the user can submit the model.
     */
    public function submit(User $user, WorkflowRequest $request): bool
    {
        // Only the request owner can submit draft requests
        return $request->user_id === $user->id && $request->status === 'draft';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, WorkflowRequest $request): bool
    {
        // Check if user is an approver for this request
        return $request->approvals()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }
}
