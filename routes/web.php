<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Admin\DepartmentWorkflowController;
use App\Http\Controllers\Auth\AutoLoginController;
use App\Http\Controllers\Admin\UserManagementController;

Route::get('/', function () {
	return view('welcome');
});

Route::get('/autologin', AutoLoginController::class)->name('autologin');

Route::prefix('requests')->group(function () {
	Route::get('/', [RequestController::class, 'index'])->name('requests.index');
	Route::get('/create', [RequestController::class, 'create'])->name('requests.create');
	Route::post('/', [RequestController::class, 'store'])->name('requests.store');
});

Route::prefix('approvals')->group(function () {
	Route::get('/inbox', [ApprovalController::class, 'inbox'])->name('approvals.inbox');
	Route::post('/{approval}/decide', [ApprovalController::class, 'decide'])->name('approvals.decide');
});

Route::prefix('admin')->group(function () {
	Route::get('/workflows', [DepartmentWorkflowController::class, 'index'])->name('admin.workflows.index');
	Route::post('/workflows', [DepartmentWorkflowController::class, 'store'])->name('admin.workflows.store');
	Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
	Route::post('/users', [UserManagementController::class, 'store'])->name('admin.users.store');
});
