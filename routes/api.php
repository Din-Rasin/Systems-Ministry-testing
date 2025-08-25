<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\RequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API routes for the workflow application
Route::middleware(['api.throttle:60,1'])->group(function () {
    // Workflow requests
    Route::get('/requests', [WorkflowController::class, 'index'])->name('api.requests.index');
    Route::get('/requests/{request}', [WorkflowController::class, 'show'])->name('api.requests.show');

    // Leave requests
    Route::post('/requests/leave', [WorkflowController::class, 'storeLeave'])->name('api.requests.store-leave');

    // Mission requests
    Route::post('/requests/mission', [WorkflowController::class, 'storeMission'])->name('api.requests.store-mission');

    // Approvals
    Route::get('/approvals/pending', [WorkflowController::class, 'pendingApprovals'])->name('api.approvals.pending');
    Route::post('/approvals/{approvalId}', [WorkflowController::class, 'processApproval'])->name('api.approvals.process');

    // Messages
    Route::post('/messages', [WorkflowController::class, 'storeMessage'])->name('api.messages.store');

    // Test route
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working']);
    });

    // Workflow templates
    Route::get('/workflows/templates', [WorkflowController::class, 'getTemplates'])->name('api.workflows.templates');
    Route::get('/workflows/templates/{id}', [WorkflowController::class, 'getTemplate'])->name('api.workflows.template');

    // Workflows
    Route::get('/workflows', [WorkflowController::class, 'list'])->name('api.workflows.list');
    Route::post('/workflows', [WorkflowController::class, 'create'])->name('api.workflows.create');
    Route::get('/workflows/{workflow}', [WorkflowController::class, 'view'])->name('api.workflows.view');
    Route::put('/workflows/{workflow}', [WorkflowController::class, 'edit'])->name('api.workflows.edit');
    Route::delete('/workflows/{workflow}', [WorkflowController::class, 'delete'])->name('api.workflows.delete');

    // Requests
    Route::get('/requests', [RequestController::class, 'index'])->name('api.requests.index');
    Route::post('/requests/leave', [RequestController::class, 'storeLeave'])->name('api.requests.store-leave');
    Route::post('/requests/mission', [RequestController::class, 'storeMission'])->name('api.requests.store-mission');
    Route::get('/requests/{request}', [RequestController::class, 'show'])->name('api.requests.show');
    Route::put('/requests/{request}', [RequestController::class, 'update'])->name('api.requests.update');
    Route::delete('/requests/{request}', [RequestController::class, 'destroy'])->name('api.requests.destroy');
    Route::post('/requests/{request}/submit', [RequestController::class, 'submit'])->name('api.requests.submit');

    // Workflow visualization
    Route::get('/requests/{requestId}/workflow-visualization', [WorkflowController::class, 'getWorkflowVisualization'])->name('api.requests.workflow-visualization');
});
