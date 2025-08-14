<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Workflow Management System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .badge-status-pending { background-color: #ffc107; }
        .badge-status-approved { background-color: #28a745; }
        .badge-status-rejected { background-color: #dc3545; }
        .badge-status-cancelled { background-color: #6c757d; }
        .workflow-step {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .workflow-step.current {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        .workflow-step.completed {
            background-color: #e8f5e8;
            border-left: 4px solid #4caf50;
        }
        .workflow-step.pending {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        .workflow-step.rejected {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @auth
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="d-flex flex-column">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            <i class="bi bi-diagram-2-fill"></i>
                            Workflow System
                        </h5>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                        <a class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" href="{{ route('requests.index') }}">
                            <i class="bi bi-file-earmark-text"></i> My Requests
                        </a>
                        
                        <a class="nav-link" href="{{ route('requests.create', ['type' => 'leave']) }}">
                            <i class="bi bi-plus-circle"></i> Submit Leave Request
                        </a>
                        
                        <a class="nav-link" href="{{ route('requests.create', ['type' => 'mission']) }}">
                            <i class="bi bi-plus-circle"></i> Submit Mission Request
                        </a>
                        
                        @if(auth()->user()->hasRole('team_leader') || auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('cfo') || auth()->user()->hasRole('ceo'))
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                        <a class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}" href="{{ route('approvals.index') }}">
                            <i class="bi bi-check-circle"></i> Pending Approvals
                            <span class="badge bg-warning text-dark ms-2" id="pending-count">0</span>
                        </a>
                        @endif
                        
                        @if(auth()->user()->hasRole('system_admin') || auth()->user()->hasRole('dept_admin'))
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                        <div class="nav-link text-white-50 fw-bold">
                            <i class="bi bi-gear"></i> Administration
                        </div>
                        
                        @if(auth()->user()->hasRole('system_admin'))
                        <a class="nav-link ps-4" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i> Manage Users
                        </a>
                        @endif
                        
                        <a class="nav-link ps-4" href="{{ route('admin.workflows.index') }}">
                            <i class="bi bi-diagram-3"></i> Manage Workflows
                        </a>
                        @endif
                    </nav>
                    
                    <div class="mt-auto">
                        <hr style="border-color: rgba(255,255,255,0.2);">
                        <div class="text-white-50 small">
                            Welcome, {{ auth()->user()->name }}<br>
                            <small>{{ auth()->user()->employee_id }}</small>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm w-100">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4 @guest offset-0 col-12 @endguest">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js for dashboard charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // CSRF token setup for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Update pending approvals count
        @auth
        if (document.getElementById('pending-count')) {
            fetch('/api/approvals/stats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pending-count').textContent = data.pending_count;
                    if (data.pending_count === 0) {
                        document.getElementById('pending-count').style.display = 'none';
                    }
                })
                .catch(error => console.log('Failed to fetch approval stats'));
        }
        @endauth
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                }
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>