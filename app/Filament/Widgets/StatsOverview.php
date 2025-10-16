<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Request;
use App\Models\LeaveRequest;
use App\Models\MissionRequest;
use App\Models\Department;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users in the system')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Active Departments', Department::where('is_active', true)->count())
                ->description('Currently active departments')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
                
            Stat::make('Pending Requests', Request::where('status', 'pending')->count())
                ->description('Requests awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Leave Requests', LeaveRequest::count())
                ->description('Total leave requests submitted')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
                
            Stat::make('Mission Requests', MissionRequest::count())
                ->description('Total mission requests submitted')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('secondary'),
                
            Stat::make('Approved This Month', Request::where('status', 'approved')
                ->whereMonth('updated_at', now()->month)
                ->count())
                ->description('Requests approved this month')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}