<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $credentials['remember'] ?? false)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.index'))
                ->with('success', 'Logged in successfully.');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully.');
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        $departments = Department::where('is_active', true)->get();
        $roles = Role::where('is_active', true)->get();

        return view('auth.register', compact('departments', 'roles'));
    }

    /**
     * Handle registration request.
     */
    public function register(RegisterRequest $request)
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

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Show the password reset form.
     */
    public function showPasswordResetForm()
    {
        return view('auth.password-reset');
    }

    /**
     * Handle password reset request.
     */
    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // In a real application, you would send a password reset email here
        // For now, we'll just show a success message
        return redirect()->back()
            ->with('success', 'Password reset link sent to your email.');
    }

    /**
     * Show the password change form.
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change request.
     */
    public function changePassword(Request $request)
    {
        $validatedData = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        // Update password
        $user->password = Hash::make($validatedData['new_password']);
        $user->save();

        return redirect()->back()
            ->with('success', 'Password changed successfully.');
    }
}
