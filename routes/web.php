<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WorkflowController as AdminWorkflowController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    
    // Requests
    Route::resource('requests', RequestController::class);
    Route::get('/requests/{request}/download', [RequestController::class, 'download'])->name('requests.download');
    
    // Approvals
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/{request}', [ApprovalController::class, 'show'])->name('show');
        Route::post('/{request}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{request}/reject', [ApprovalController::class, 'reject'])->name('reject');
        Route::get('/statistics/data', [ApprovalController::class, 'statistics'])->name('statistics');
        Route::post('/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('bulk-approve');
    });
    
    // Admin routes (for system administrators and department administrators)
    Route::middleware('can:admin-access')->prefix('admin')->name('admin.')->group(function () {
        // User management
        Route::resource('users', AdminUserController::class);
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('/users/{user}/remove-role', [AdminUserController::class, 'removeRole'])->name('users.remove-role');
        Route::post('/users/{user}/assign-department', [AdminUserController::class, 'assignDepartment'])->name('users.assign-department');
        
        // Workflow management
        Route::resource('workflows', AdminWorkflowController::class);
        Route::post('/workflows/{workflow}/toggle-status', [AdminWorkflowController::class, 'toggleStatus'])->name('workflows.toggle-status');
    });
});

// API routes for AJAX requests
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'data']);
    Route::get('/approvals/stats', [ApprovalController::class, 'statistics']);
});
