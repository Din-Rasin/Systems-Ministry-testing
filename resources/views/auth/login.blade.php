@extends('layouts.app')

@section('title', 'Login - Workflow Management System')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">
                            <i class="bi bi-diagram-2-fill"></i>
                            Workflow System
                        </h2>
                        <p class="text-muted">Sign in to manage your requests</p>
                    </div>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                                placeholder="Enter your email"
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Enter your password"
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Demo Credentials -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Demo Credentials</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">System Admin</h6>
                            <p class="small mb-1"><strong>Email:</strong> admin@company.com</p>
                            <p class="small mb-2"><strong>Password:</strong> password</p>
                            
                            <h6 class="text-muted">Employee (IT)</h6>
                            <p class="small mb-1"><strong>Email:</strong> charlie@company.com</p>
                            <p class="small mb-2"><strong>Password:</strong> password</p>
                            
                            <h6 class="text-muted">Team Leader (IT)</h6>
                            <p class="small mb-1"><strong>Email:</strong> it-leader@company.com</p>
                            <p class="small"><strong>Password:</strong> password</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">HR Manager</h6>
                            <p class="small mb-1"><strong>Email:</strong> hr@company.com</p>
                            <p class="small mb-2"><strong>Password:</strong> password</p>
                            
                            <h6 class="text-muted">CFO</h6>
                            <p class="small mb-1"><strong>Email:</strong> cfo@company.com</p>
                            <p class="small mb-2"><strong>Password:</strong> password</p>
                            
                            <h6 class="text-muted">CEO</h6>
                            <p class="small mb-1"><strong>Email:</strong> ceo@company.com</p>
                            <p class="small"><strong>Password:</strong> password</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Quick login function for demo
    function quickLogin(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
    }
    
    // Add click handlers to demo credentials
    document.addEventListener('DOMContentLoaded', function() {
        const emails = document.querySelectorAll('p:contains("Email:")');
        emails.forEach(function(element) {
            if (element.textContent.includes('Email:')) {
                const email = element.textContent.split('Email: ')[1];
                element.style.cursor = 'pointer';
                element.onclick = () => quickLogin(email);
                element.title = 'Click to auto-fill';
            }
        });
    });
</script>
@endsection