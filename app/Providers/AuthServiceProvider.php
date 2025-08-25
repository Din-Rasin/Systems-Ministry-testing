<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Workflow;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \App\Models\Request::class => \App\Policies\RequestPolicy::class,
        \App\Models\Approval::class => \App\Policies\ApprovalPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define custom gates for workflow actions
        Gate::define('manage-workflows', function (User $user) {
            // Only admins and department heads can manage workflows
            return $user->hasAnyRole(['System Administrator', 'Department Administrator']);
        });

        Gate::define('view-workflow', function (User $user, Workflow $workflow) {
            // Users can view workflows in their department
            return $user->departments()->where('id', $workflow->department_id)->exists();
        });

        Gate::define('create-workflow', function (User $user) {
            // Only admins and department heads can create workflows
            return $user->hasAnyRole(['System Administrator', 'Department Administrator']);
        });

        Gate::define('update-workflow', function (User $user, Workflow $workflow) {
            // Only admins and department heads can update workflows
            // And only for their department
            return $user->hasAnyRole(['System Administrator', 'Department Administrator']) &&
                   $user->departments()->where('id', $workflow->department_id)->exists();
        });

        Gate::define('delete-workflow', function (User $user, Workflow $workflow) {
            // Only admins can delete workflows
            // And only for their department
            return $user->hasRole('System Administrator') &&
                   $user->departments()->where('id', $workflow->department_id)->exists();
        });
    }
}
