<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
	public function index(): View
	{
		$users = User::with(['departments', 'roles'])->orderBy('name')->get();
		$departments = Department::orderBy('name')->get();
		$roles = Role::orderBy('name')->get();
		return view('admin.users.index', compact('users', 'departments', 'roles'));
	}

	public function store(Request $request): RedirectResponse
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|string|min:6',
			'department_ids' => 'array',
			'department_ids.*' => 'exists:departments,id',
			'role_ids' => 'array',
			'role_ids.*' => 'exists:roles,id',
		]);

		$user = User::create([
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => Hash::make($data['password']),
		]);

		if (!empty($data['department_ids'])) {
			$user->departments()->sync($data['department_ids']);
		}
		if (!empty($data['role_ids'])) {
			$user->roles()->sync($data['role_ids']);
		}

		return back()->with('status', 'User created');
	}
}