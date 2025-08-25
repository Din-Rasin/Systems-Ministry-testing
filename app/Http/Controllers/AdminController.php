<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $users = User::with(['departments', 'roles'])
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->department, function ($query, $departmentId) {
                return $query->whereHas('departments', function ($subQuery) use ($departmentId) {
                    $subQuery->where('departments.id', $departmentId);
                });
            })
            ->when($request->role, function ($query, $roleId) {
                return $query->whereHas('roles', function ($subQuery) use ($roleId) {
                    $subQuery->where('roles.id', $roleId);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $departments = Department::where('is_active', true)->get();
        $roles = Role::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $roles = Role::where('is_active', true)->get();

        return view('admin.users.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $validatedData = $request->validated();

        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Assign department and role
        $user->departments()->attach($validatedData['department_id'], [
            'role_id' => $validatedData['role_id'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['departments', 'roles', 'requests', 'approvals']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['departments', 'roles']);
        $departments = Department::where('is_active', true)->get();
        $roles = Role::where('is_active', true)->get();

        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        // Update user details
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
        ]);

        // Update password if provided
        if (!empty($validatedData['password'])) {
            $user->update([
                'password' => Hash::make($validatedData['password']),
            ]);
        }

        // Update department and role assignment
        if (isset($validatedData['department_id']) && isset($validatedData['role_id'])) {
            // Remove existing department/role assignments
            $user->departments()->detach();

            // Assign new department and role
            $user->departments()->attach($validatedData['department_id'], [
                'role_id' => $validatedData['role_id'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Check if user has any requests or approvals
        if ($user->requests()->exists() || $user->approvals()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user with existing requests or approvals.');
        }

        // Remove department/role assignments
        $user->departments()->detach();

        // Delete the user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(User $user)
    {
        // Deactivate user's department/role assignments
        $user->departments()->updateExistingPivot($user->department_id, [
            'is_active' => false,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deactivated successfully.');
    }

    /**
     * Activate the specified user.
     */
    public function activate(User $user)
    {
        // Activate user's department/role assignments
        $user->departments()->updateExistingPivot($user->department_id, [
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User activated successfully.');
    }

    /**
     * Display a listing of departments.
     */
    public function departments()
    {
        try {
            $departments = Department::orderBy('name')->paginate(10);
            return view('departments.index', compact('departments'));
        } catch (\Exception $e) {
            Log::error('Department index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')->with('error', 'Unable to load departments. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new department.
     */
    public function createDepartment()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function storeDepartment(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:departments',
                'description' => 'nullable|string',
            ]);

            $department = Department::create($validatedData);

            return redirect()->route('admin.departments.index')
                ->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            Log::error('Department store error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Unable to create department. Please try again later.')
                ->withInput();
        }
    }

    /**
     * Display the specified department.
     */
    public function showDepartment(Department $department)
    {
        try {
            $department->load(['users', 'workflows.requestType']);
            return view('departments.show', compact('department'));
        } catch (\Exception $e) {
            Log::error('Department show error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'department_id' => $department->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.departments.index')->with('error', 'Unable to load department. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified department.
     */
    public function editDepartment(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function updateDepartment(Request $request, Department $department)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
                'description' => 'nullable|string',
            ]);

            $department->update($validatedData);

            return redirect()->route('admin.departments.index')
                ->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            Log::error('Department update error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'department_id' => $department->id,
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Unable to update department. Please try again later.')
                ->withInput();
        }
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroyDepartment(Department $department)
    {
        try {
            // Check if department has any users or workflows
            if ($department->users()->exists() || $department->workflows()->exists()) {
                return redirect()->route('admin.departments.index')
                    ->with('error', 'Cannot delete department with existing users or workflows.');
            }

            $department->delete();

            return redirect()->route('admin.departments.index')
                ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Department destroy error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'department_id' => $department->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.departments.index')
                ->with('error', 'Unable to delete department. Please try again later.');
        }
    }

    /**
     * Show the workflows for a specific department.
     */
    public function manageDepartmentWorkflows(Department $department)
    {
        try {
            $department->load('workflows.requestType');
            return view('departments.manage-workflows', compact('department'));
        } catch (\Exception $e) {
            Log::error('Manage department workflows error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'department_id' => $department->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.departments.index')->with('error', 'Unable to load department workflows. Please try again later.');
        }
    }
}
