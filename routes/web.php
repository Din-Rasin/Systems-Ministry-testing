<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FileController;

// Default route - show login page for guests, dashboard for authenticated users
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Password reset routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{role}', [DashboardController::class, 'index'])->name('dashboard.role');

    // Requests
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::get('/requests/create/leave', [RequestController::class, 'createLeave'])->name('requests.create-leave');
    Route::get('/requests/create/mission', [RequestController::class, 'createMission'])->name('requests.create-mission');
    Route::post('/requests/leave', [RequestController::class, 'storeLeave'])->name('requests.store-leave');
    Route::post('/requests/mission', [RequestController::class, 'storeMission'])->name('requests.store-mission');
    Route::get('/requests/{request}', [RequestController::class, 'show'])->name('requests.show');
    Route::get('/requests/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::put('/requests/{request}', [RequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{request}', [RequestController::class, 'destroy'])->name('requests.destroy');
    Route::post('/requests/{request}/submit', [RequestController::class, 'submit'])->name('requests.submit');
    Route::post('/requests/{request}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
    Route::post('/requests/{request}/messages', [RequestController::class, 'storeMessage'])->name('requests.store-message');

    // Workflows (admin only)
    Route::middleware(['role:System Administrator,Department Administrator'])->group(function () {
        Route::get('/workflows', [WorkflowController::class, 'index'])->name('workflows.index');
        Route::get('/workflows/create', [WorkflowController::class, 'create'])->name('workflows.create');
        Route::post('/workflows', [WorkflowController::class, 'store'])->name('workflows.store');
        Route::get('/workflows/{workflow}', [WorkflowController::class, 'show'])->name('workflows.show');
        Route::get('/workflows/{workflow}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
        Route::put('/workflows/{workflow}', [WorkflowController::class, 'update'])->name('workflows.update');
        Route::delete('/workflows/{workflow}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');
        Route::get('/workflows/{workflow}/design', [WorkflowController::class, 'design'])->name('workflows.design');
        Route::post('/workflows/{workflow}/save-design', [WorkflowController::class, 'saveDesign'])->name('workflows.save-design');
        Route::get('/workflow-diagram', [WorkflowController::class, 'diagram'])->name('workflow.diagram');
        Route::get('/workflow-flowchart', [WorkflowController::class, 'flowchart'])->name('workflow.flowchart');
        Route::get('/requests/{requestId}/workflow-visualization', [WorkflowController::class, 'visualize'])->name('requests.workflow-visualization');
    });

    // Approvals
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::get('/approvals/all', [ApprovalController::class, 'all'])->name('approvals.all');

    // Reports (admin only)
    Route::middleware(['role:System Administrator,Department Administrator'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/request-statistics', [ReportController::class, 'requestStatistics'])->name('reports.request-statistics');
        Route::get('/reports/approval-statistics', [ReportController::class, 'approvalStatistics'])->name('reports.approval-statistics');
        Route::get('/reports/workflow-performance', [ReportController::class, 'workflowPerformance'])->name('reports.workflow-performance');
        Route::get('/reports/department-performance', [ReportController::class, 'departmentPerformance'])->name('reports.department-performance');
        Route::get('/reports/user-activity', [ReportController::class, 'userActivity'])->name('reports.user-activity');
        Route::get('/reports/workflow-analysis', [ReportController::class, 'workflowAnalysis'])->name('reports.workflow-analysis');
        Route::get('/reports/system-usage', [ReportController::class, 'systemUsage'])->name('reports.system-usage');
        Route::get('/reports/export-requests', [ReportController::class, 'exportRequests'])->name('reports.export-requests');
        Route::get('/reports/export-approvals', [ReportController::class, 'exportApprovals'])->name('reports.export-approvals');
    });

    // File management
    Route::get('/files', [FileController::class, 'index'])->name('files.index');
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{filename}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/files/{filename}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::delete('/files/{filename}', [FileController::class, 'destroy'])->name('files.destroy');

    // Admin routes (system admin only)
    Route::middleware(['role:System Administrator'])->group(function () {
        Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}', [AdminController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{user}/deactivate', [AdminController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::post('/admin/users/{user}/activate', [AdminController::class, 'activate'])->name('admin.users.activate');

        // Department management
        Route::get('/admin/departments', [AdminController::class, 'departments'])->name('admin.departments.index');
        Route::get('/admin/departments/create', [AdminController::class, 'createDepartment'])->name('admin.departments.create');
        Route::post('/admin/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
        Route::get('/admin/departments/{department}/edit', [AdminController::class, 'editDepartment'])->name('admin.departments.edit');
        Route::put('/admin/departments/{department}', [AdminController::class, 'updateDepartment'])->name('admin.departments.update');
        Route::delete('/admin/departments/{department}', [AdminController::class, 'destroyDepartment'])->name('admin.departments.destroy');
        Route::get('/admin/departments/{department}/manage-workflows', [AdminController::class, 'manageDepartmentWorkflows'])->name('admin.departments.manage-workflows');

        // Role management
        Route::get('/admin/roles', [AdminController::class, 'roles'])->name('admin.roles.index');
        Route::get('/admin/roles/create', [AdminController::class, 'createRole'])->name('admin.roles.create');
        Route::post('/admin/roles', [AdminController::class, 'storeRole'])->name('admin.roles.store');
        Route::get('/admin/roles/{role}/edit', [AdminController::class, 'editRole'])->name('admin.roles.edit');
        Route::put('/admin/roles/{role}', [AdminController::class, 'updateRole'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [AdminController::class, 'destroyRole'])->name('admin.roles.destroy');

        // System Settings
        Route::resource('admin/system-settings', App\Http\Controllers\Admin\SystemSettingController::class)->except(['show']);

        // Holidays
        Route::resource('admin/holidays', App\Http\Controllers\Admin\HolidayController::class)->except(['show']);

        // Notification Templates
        Route::resource('admin/notification-templates', App\Http\Controllers\Admin\NotificationTemplateController::class)->except(['show']);

        // Workflow Rules
        Route::resource('admin/workflow-rules', App\Http\Controllers\Admin\WorkflowRuleController::class)->except(['show']);

        // Audit Logs
        Route::resource('admin/audit-logs', App\Http\Controllers\Admin\AuditLogController::class)->only(['index', 'show']);
        Route::delete('admin/audit-logs/clear', [App\Http\Controllers\Admin\AuditLogController::class, 'clear'])->name('admin.audit-logs.clear');

        // Integrations
        Route::resource('admin/integrations', App\Http\Controllers\Admin\IntegrationController::class)->except(['show']);
        Route::post('admin/integrations/{integration}/test-connection', [App\Http\Controllers\Admin\IntegrationController::class, 'testConnection'])->name('admin.integrations.test-connection');
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/delete-all-read', [NotificationController::class, 'destroyAllRead'])->name('notifications.destroy-all-read');

    // Profile routes
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.update-password');

    // Documentation routes
    Route::get('/docs/workflow-diagram', function () {
        return view('docs.workflow-diagram');
    })->name('docs.workflow-diagram');

    Route::get('/docs/workflow-flowchart', function () {
        return view('docs.workflow-flowchart');
    })->name('docs.workflow-flowchart');

    Route::get('/docs/ui-components', function () {
        // Create a sample user for the example
        $sampleUser = (object) [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'created_at' => now(),
            'activeRoles' => collect([
                (object) ['name' => 'User'],
                (object) ['name' => 'Team Leader']
            ])
        ];
        return view('components.user-profile-card-example', compact('sampleUser'));
    })->name('docs.ui-components');
});

// Test routes (not protected by auth)
Route::get('/test/user-profile-card', function () {
    // Get a real user for testing
    $user = \App\Models\User::first();
    if (!$user) {
        // Create a sample user if none exists
        $user = (object) [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'created_at' => now(),
            'activeRoles' => collect([
                (object) ['name' => 'User'],
                (object) ['name' => 'Team Leader']
            ])
        ];
    }
    return view('components.user-profile-card-example', compact('user'));
})->name('test.user-profile-card');
