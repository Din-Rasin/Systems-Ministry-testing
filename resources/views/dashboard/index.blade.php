@extends('layouts.app')

@section('title', 'Dashboard - Workflow Management System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('requests.create', ['type' => 'leave']) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Submit Leave Request
        </a>
        <a href="{{ route('requests.create', ['type' => 'mission']) }}" class="btn btn-outline-primary">
            <i class="bi bi-plus-circle"></i> Submit Mission Request
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Pending Requests</h6>
                        <h2 class="mb-0">{{ $stats['pending_requests'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Approved Requests</h6>
                        <h2 class="mb-0">{{ $stats['approved_requests'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Rejected Requests</h6>
                        <h2 class="mb-0">{{ $stats['rejected_requests'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Pending Approvals</h6>
                        <h2 class="mb-0">{{ $stats['pending_approvals'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-list-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Requests -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Recent Requests</h5>
                <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRequests as $request)
                            <tr>
                                <td>
                                    <code>{{ $request->request_number }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $request->type === 'leave' ? 'info' : 'warning' }}">
                                        {{ ucfirst($request->type) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($request->title, 40) }}</td>
                                <td>
                                    <span class="badge badge-status-{{ $request->status }}">
                                        {{ $request->getFormattedStatus() }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No requests found. <a href="{{ route('requests.create') }}">Submit your first request</a></p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Pending Approvals (if user has approval rights) -->
    <div class="col-md-4">
        @if(auth()->user()->hasRole('team_leader') || auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('cfo') || auth()->user()->hasRole('ceo'))
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Pending Approvals</h5>
                <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body">
                @if($pendingApprovals->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($pendingApprovals->take(5) as $approval)
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $approval->request->title }}</h6>
                            <small>{{ $approval->request->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">
                            <span class="badge bg-{{ $approval->request->type === 'leave' ? 'info' : 'warning' }}">
                                {{ ucfirst($approval->request->type) }}
                            </span>
                            by {{ $approval->request->user->name }}
                        </p>
                        <small>Step: {{ $approval->workflowStep->name }}</small>
                        <div class="mt-2">
                            <a href="{{ route('approvals.show', $approval->request) }}" class="btn btn-sm btn-outline-primary">
                                Review
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No pending approvals</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('requests.create', ['type' => 'leave']) }}" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-event"></i> Submit Leave Request
                    </a>
                    <a href="{{ route('requests.create', ['type' => 'mission']) }}" class="btn btn-outline-warning">
                        <i class="bi bi-airplane"></i> Submit Mission Request
                    </a>
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list"></i> View My Requests
                    </a>
                    @if(auth()->user()->hasRole('team_leader') || auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('cfo') || auth()->user()->hasRole('ceo'))
                    <a href="{{ route('approvals.index') }}" class="btn btn-outline-info">
                        <i class="bi bi-check-square"></i> Review Approvals
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Status Chart -->
@if($stats['pending_requests'] + $stats['approved_requests'] + $stats['rejected_requests'] > 0)
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Request Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> System Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Department:</dt>
                    <dd class="col-sm-6">{{ auth()->user()->primaryDepartment()?->name ?? 'Not assigned' }}</dd>
                    
                    <dt class="col-sm-6">Employee ID:</dt>
                    <dd class="col-sm-6">{{ auth()->user()->employee_id }}</dd>
                    
                    <dt class="col-sm-6">Roles:</dt>
                    <dd class="col-sm-6">
                        @foreach(auth()->user()->roles as $role)
                        <span class="badge bg-secondary me-1">{{ $role->display_name }}</span>
                        @endforeach
                    </dd>
                    
                    <dt class="col-sm-6">Last Login:</dt>
                    <dd class="col-sm-6">{{ now()->format('M d, Y H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Status Chart
    @if($stats['pending_requests'] + $stats['approved_requests'] + $stats['rejected_requests'] > 0)
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $stats['pending_requests'] }}, 
                    {{ $stats['approved_requests'] }}, 
                    {{ $stats['rejected_requests'] }}
                ],
                backgroundColor: [
                    '#ffc107',
                    '#28a745',
                    '#dc3545'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    @endif
    
    // Real-time updates every 30 seconds
    setInterval(function() {
        fetch('/dashboard/data')
            .then(response => response.json())
            .then(data => {
                // Update pending approvals count in sidebar
                const pendingCountEl = document.getElementById('pending-count');
                if (pendingCountEl) {
                    pendingCountEl.textContent = data.pending_approvals_count;
                    pendingCountEl.style.display = data.pending_approvals_count > 0 ? 'inline' : 'none';
                }
            })
            .catch(error => console.log('Failed to fetch dashboard data'));
    }, 30000);
</script>
@endpush