<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Request as WorkflowRequest;
use App\Models\Approval;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index($role = null)
    {
        try {
            $user = Auth::user();

            // Get user's primary role
            $userRole = $user->activeRoles()->first();
            $roleName = $userRole ? $userRole->name : 'User';

            // If no specific role is requested, redirect to the user's primary role dashboard
            if (!$role) {
                return redirect()->route('dashboard.' . strtolower(str_replace(' ', '-', $roleName)));
            }

            // If a specific role is requested, check if the user has that role
            $requestedRole = str_replace('-', ' ', $role);
            if (!$user->hasRole($requestedRole)) {
                // If user doesn't have the requested role, redirect to their primary role dashboard
                return redirect()->route('dashboard.' . strtolower(str_replace(' ', '-', $roleName)));
            }
            $roleName = $requestedRole;

            // Common data for all users (with caching)
            $cacheKey = "dashboard_data_{$user->id}";
            $dashboardData = Cache::remember($cacheKey, 300, function () use ($user) {
                return [
                    'requests' => $user->requests()->latest()->take(5)->get(),
                    'pendingApprovals' => $user->approvals()->where('status', 'pending')->count(),
                    'unreadNotifications' => $user->notifications()->whereNull('read_at')->count(),
                    'totalRequests' => $user->requests()->count(),
                ];
            });

            // Role-specific data (with caching)
            $roleCacheKey = "role_data_{$user->id}_{$roleName}";
            $roleSpecificData = Cache::remember($roleCacheKey, 300, function () use ($user, $roleName) {
                return $this->getRoleSpecificData($user, $roleName);
            });

            return view('dashboards.' . strtolower(str_replace(' ', '-', $roleName)), array_merge(
                compact(
                    'user',
                    'roleName'
                ),
                $dashboardData,
                compact('roleSpecificData')
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'role' => $role,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')->with('error', 'Unable to load dashboard. Please try again later.');
        }
    }

    /**
     * Get role-specific dashboard data.
     */
    protected function getRoleSpecificData($user, $roleName)
    {
        try {
            switch ($roleName) {
                case 'Team Leader':
                    return $this->getTeamLeaderData($user);
                case 'HR Manager':
                    return $this->getHRManagerData($user);
                case 'CFO':
                    return $this->getCFOData($user);
                case 'CEO':
                    return $this->getCEOData($user);
                case 'Department Administrator':
                    return $this->getDepartmentAdminData($user);
                case 'System Administrator':
                    return $this->getSystemAdminData($user);
                default:
                    return $this->getUserData($user);
            }
        } catch (\Exception $e) {
            Log::error('Role specific data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'role' => $roleName,
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty data structure on error
            return [];
        }
    }

    /**
     * Get data for regular users.
     */
    protected function getUserData($user)
    {
        return [
            'leaveBalance' => $this->getLeaveBalance($user),
            'upcomingLeaves' => $this->getUpcomingLeaves($user),
        ];
    }

    /**
     * Get data for team leaders.
     */
    protected function getTeamLeaderData($user)
    {
        try {
            $departmentUsers = $user->department->users()->where('id', '!=', $user->id)->get();

            return [
                'teamRequests' => WorkflowRequest::whereIn('user_id', $departmentUsers->pluck('id'))
                    ->where('status', 'pending')
                    ->count(),
                'teamMembers' => $departmentUsers->count(),
                'pendingApprovals' => Approval::where('approver_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Team leader data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'teamRequests' => 0,
                'teamMembers' => 0,
                'pendingApprovals' => 0,
            ];
        }
    }

    /**
     * Get data for HR managers.
     */
    protected function getHRManagerData($user)
    {
        try {
            return [
                'pendingHRApprovals' => Approval::whereHas('step.role', function ($query) {
                        $query->where('name', 'HR Manager');
                    })
                    ->where('status', 'pending')
                    ->count(),
                'departmentRequests' => WorkflowRequest::whereHas('user', function ($query) use ($user) {
                        $query->where('department_id', $user->department_id);
                    })
                    ->where('status', 'approved')
                    ->count(),
                'leaveStatistics' => $this->getLeaveStatistics($user),
            ];
        } catch (\Exception $e) {
            Log::error('HR manager data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'pendingHRApprovals' => 0,
                'departmentRequests' => 0,
                'leaveStatistics' => [
                    'approved' => 0,
                    'pending' => 0,
                ],
            ];
        }
    }

    /**
     * Get data for CFOs.
     */
    protected function getCFOData($user)
    {
        try {
            return [
                'pendingCFOApprovals' => Approval::whereHas('step.role', function ($query) {
                        $query->where('name', 'CFO');
                    })
                    ->where('status', 'pending')
                    ->count(),
                'missionRequests' => WorkflowRequest::where('type', 'mission')
                    ->where('status', 'pending')
                    ->count(),
                'budgetStatistics' => $this->getBudgetStatistics($user),
            ];
        } catch (\Exception $e) {
            Log::error('CFO data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'pendingCFOApprovals' => 0,
                'missionRequests' => 0,
                'budgetStatistics' => [
                    'totalBudget' => 0,
                    'approvedMissions' => 0,
                ],
            ];
        }
    }

    /**
     * Get data for CEOs.
     */
    protected function getCEOData($user)
    {
        try {
            return [
                'pendingCEOApprovals' => Approval::whereHas('step.role', function ($query) {
                        $query->where('name', 'CEO');
                    })
                    ->where('status', 'pending')
                    ->count(),
                'totalDepartments' => Department::count(),
                'systemStatistics' => $this->getSystemStatistics(),
            ];
        } catch (\Exception $e) {
            Log::error('CEO data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'pendingCEOApprovals' => 0,
                'totalDepartments' => 0,
                'systemStatistics' => [
                    'totalRequests' => 0,
                    'totalApprovals' => 0,
                    'activeUsers' => 0,
                ],
            ];
        }
    }

    /**
     * Get data for department administrators.
     */
    protected function getDepartmentAdminData($user)
    {
        try {
            return [
                'departmentUsers' => $user->department->users()->count(),
                'departmentWorkflows' => $user->department->workflows()->count(),
                'pendingDepartmentRequests' => WorkflowRequest::whereHas('user', function ($query) use ($user) {
                        $query->where('department_id', $user->department_id);
                    })
                    ->where('status', 'pending')
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Department admin data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'departmentUsers' => 0,
                'departmentWorkflows' => 0,
                'pendingDepartmentRequests' => 0,
            ];
        }
    }

    /**
     * Get data for system administrators.
     */
    protected function getSystemAdminData($user)
    {
        try {
            return [
                'totalUsers' => \App\Models\User::count(),
                'totalDepartments' => Department::count(),
                'totalWorkflows' => \App\Models\Workflow::count(),
                'systemHealth' => $this->getSystemHealth(),
            ];
        } catch (\Exception $e) {
            Log::error('System admin data error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'totalUsers' => 0,
                'totalDepartments' => 0,
                'totalWorkflows' => 0,
                'systemHealth' => [
                    'status' => 'unknown',
                    'uptime' => '0%',
                ],
            ];
        }
    }

    /**
     * Get leave balance for a user.
     */
    protected function getLeaveBalance($user)
    {
        try {
            // This would be implemented based on your leave balance system
            return [
                'annual' => 20,
                'used' => 5,
                'remaining' => 15,
            ];
        } catch (\Exception $e) {
            Log::error('Leave balance error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'annual' => 0,
                'used' => 0,
                'remaining' => 0,
            ];
        }
    }

    /**
     * Get upcoming leaves for a user.
     */
    protected function getUpcomingLeaves($user)
    {
        try {
            return WorkflowRequest::where('user_id', $user->id)
                ->where('type', 'leave')
                ->where('status', 'approved')
                ->whereHas('leaveRequest', function ($query) {
                    $query->where('start_date', '>=', now());
                })
                ->with('leaveRequest')
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            Log::error('Upcoming leaves error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return collect();
        }
    }

    /**
     * Get leave statistics.
     */
    protected function getLeaveStatistics($user)
    {
        try {
            return [
                'approved' => WorkflowRequest::whereHas('user', function ($query) use ($user) {
                        $query->where('department_id', $user->department_id);
                    })
                    ->where('type', 'leave')
                    ->where('status', 'approved')
                    ->count(),
                'pending' => WorkflowRequest::whereHas('user', function ($query) use ($user) {
                        $query->where('department_id', $user->department_id);
                    })
                    ->where('type', 'leave')
                    ->where('status', 'pending')
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Leave statistics error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'approved' => 0,
                'pending' => 0,
            ];
        }
    }

    /**
     * Get budget statistics.
     */
    protected function getBudgetStatistics($user)
    {
        try {
            $missionRequests = WorkflowRequest::where('type', 'mission')
                ->where('status', 'approved')
                ->with('missionRequest')
                ->get();

            $totalBudget = $missionRequests->sum(function ($request) {
                return $request->missionRequest->budget ?? 0;
            });

            return [
                'totalBudget' => $totalBudget,
                'approvedMissions' => $missionRequests->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Budget statistics error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'totalBudget' => 0,
                'approvedMissions' => 0,
            ];
        }
    }

    /**
     * Get system statistics.
     */
    protected function getSystemStatistics()
    {
        try {
            return [
                'totalRequests' => WorkflowRequest::count(),
                'totalApprovals' => Approval::count(),
                'activeUsers' => \App\Models\User::where('is_active', true)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('System statistics error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'totalRequests' => 0,
                'totalApprovals' => 0,
                'activeUsers' => 0,
            ];
        }
    }

    /**
     * Get system health information.
     */
    protected function getSystemHealth()
    {
        try {
            // This would be implemented based on your system health monitoring
            return [
                'status' => 'healthy',
                'uptime' => '99.9%',
            ];
        } catch (\Exception $e) {
            Log::error('System health error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'unknown',
                'uptime' => '0%',
            ];
        }
    }
}
