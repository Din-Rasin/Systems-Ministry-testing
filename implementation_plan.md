# Laravel Workflow Management System - Implementation Plan

## Overview

This document provides a detailed implementation plan for the Laravel Workflow Management System based on the database design created earlier.

## Phase 1: Database Implementation

### 1. Update Existing Migrations

#### Departments Table Migration

File: `database/migrations/2025_08_20_015019_create_departments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
```

#### Roles Table Migration

File: `database/migrations/2025_08_20_015407_create_roles_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
```

#### User Roles Table Migration

File: `database/migrations/2025_08_20_015416_create_user_roles_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['user_id', 'role_id']);
            $table->index(['role_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
```

#### Workflows Table Migration

File: `database/migrations/2025_08_20_015604_create_workflows_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['leave', 'mission']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
```

#### Workflow Steps Table Migration

File: `database/migrations/2025_08_20_015702_create_workflow_steps_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->integer('step_number');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['workflow_id', 'step_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
```

### 2. Create New Migrations

#### Create Leave Types Table

Command: `php artisan make:migration create_leave_types_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('max_days')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
```

#### Create Requests Table

Command: `php artisan make:migration create_requests_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['leave', 'mission']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('pending');
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->onDelete('set null');
            $table->json('data')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->foreignId('decision_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['user_id', 'type']);
            $table->index(['status']);
            $table->index(['workflow_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
```

#### Create Leave Requests Table

Command: `php artisan make:migration create_leave_requests_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('supporting_document')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
```

#### Create Mission Requests Table

Command: `php artisan make:migration create_mission_requests_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mission_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->onDelete('cascade');
            $table->string('destination');
            $table->text('purpose');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 10, 2)->default(0);
            $table->string('supporting_document')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_requests');
    }
};
```

#### Create Approvals Table

Command: `php artisan make:migration create_approvals_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('step_id')->constrained('workflow_steps')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['request_id', 'approver_id']);
            $table->index(['step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
```

#### Create Notifications Table

Command: `php artisan make:migration create_notifications_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index('user_id');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

## Phase 2: Model Implementation

### 1. User Model Enhancements

File: `app/Models/User.php`

-   Add relationships to departments, roles, requests, approvals
-   Add methods for role checking and department assignment

### 2. Department Model

File: `app/Models/Department.php`

-   Define relationships with users, roles, workflows
-   Add methods for workflow management

### 3. Role Model

File: `app/Models/Role.php`

-   Define relationships with users, departments, workflow steps
-   Add methods for permission checking

### 4. Workflow Model

File: `app/Models/Workflow.php`

-   Define relationships with department, workflow steps, requests
-   Add methods for workflow execution

### 5. WorkflowStep Model

File: `app/Models/WorkflowStep.php`

-   Define relationships with workflow, role, approvals
-   Add methods for step execution

### 6. Request Model

File: `app/Models/Request.php`

-   Define relationships with user, workflow, workflow steps, approvals
-   Add methods for request processing

### 7. LeaveRequest Model

File: `app/Models/LeaveRequest.php`

-   Extend Request model with leave-specific functionality

### 8. MissionRequest Model

File: `app/Models/MissionRequest.php`

-   Extend Request model with mission-specific functionality

### 9. Approval Model

File: `app/Models/Approval.php`

-   Define relationships with request, user, workflow step
-   Add methods for approval processing

### 10. Notification Model

File: `app/Models/Notification.php`

-   Define relationships with user
-   Add methods for notification management

## Phase 3: Authentication & Authorization

### 1. Middleware Implementation

-   Role-based access control middleware
-   Department verification middleware

### 2. Policies Implementation

-   Request access policies
-   Approval permission policies

### 3. Gates Implementation

-   Custom gates for workflow actions

## Phase 4: Controllers Implementation

### 1. AuthController

-   Handle login, logout, registration
-   Password reset functionality

### 2. DashboardController

-   Role-specific dashboard views
-   Statistics and reporting

### 3. WorkflowController

-   Workflow creation and management
-   Workflow step configuration

### 4. RequestController

-   Request submission and management
-   Leave and mission request handling

### 5. ApprovalController

-   Approval processing
-   Bulk approval operations

### 6. AdminController

-   System administration
-   User and role management

## Phase 5: Frontend Implementation

### 1. Dashboard Views

-   Role-specific dashboards
-   Request statistics and analytics
-   Pending approvals overview

### 2. Request Forms

-   Leave request form with calendar integration
-   Mission request form with file upload

### 3. Workflow Management

-   Visual workflow designer
-   Workflow step configuration

### 4. Reporting Views

-   Request statistics
-   Workflow performance metrics
-   Export functionality

## Phase 6: Advanced Features

### 1. Email Notifications

-   Job-based email notifications
-   Event listeners for request status changes

### 2. API Endpoints

-   RESTful API for mobile app integration
-   Authentication and authorization for API

### 3. Real-time Notifications

-   WebSocket integration for instant updates
-   Broadcast events for notifications

### 4. Document Management

-   File upload and storage
-   File versioning and security

## Phase 7: Security & Performance

### 1. Security Measures

-   CSRF protection on all forms
-   File upload validation and scanning
-   Rate limiting on API endpoints
-   Two-factor authentication option

### 2. Performance Optimizations

-   Database indexing strategy
-   Query optimization with eager loading
-   Caching implementation
-   Asset minification and compression

## Phase 8: Testing Strategy

### 1. Unit Tests

-   Model relationship tests
-   Business logic tests

### 2. Feature Tests

-   User workflow tests
-   Authentication tests

### 3. Browser Tests

-   Critical user journey tests
-   UI interaction tests

### 4. API Tests

-   Endpoint testing
-   Authentication testing

## Deployment & DevOps

### 1. Environment Configuration

-   Environment-specific configuration
-   Database migration scripts

### 2. Monitoring & Logging

-   Error logging and monitoring
-   Performance monitoring setup

### 3. Backup Management

-   Automated database backups
-   File backup strategies

## Success Metrics

1. User adoption rate across departments
2. Workflow completion time reduction
3. System uptime and performance
4. User satisfaction scores
5. Administrative efficiency improvements
