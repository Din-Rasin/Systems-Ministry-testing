# Laravel Workflow Management System - Database Design

## Overview

This document outlines the database schema for the Laravel Workflow Management System, which handles leave and mission requests with role-based approval workflows across different departments.

## Core Tables

### 1. Users Table

Already exists with basic structure:

-   id (bigint, primary)
-   name (string)
-   email (string, unique)
-   email_verified_at (timestamp)
-   password (string)
-   remember_token (string)
-   created_at (timestamp)
-   updated_at (timestamp)

### 2. Departments Table

Fields to add:

-   id (bigint, primary)
-   name (string)
-   description (text)
-   created_at (timestamp)
-   updated_at (timestamp)

### 3. Roles Table

Fields to add:

-   id (bigint, primary)
-   name (string) - User, Team Leader, HR Manager, CFO, CEO, Department Administrator, System Administrator
-   description (text)
-   created_at (timestamp)
-   updated_at (timestamp)

### 4. User Roles Table (Many-to-Many)

Fields to add:

-   id (bigint, primary)
-   user_id (foreign key to users)
-   role_id (foreign key to roles)
-   department_id (foreign key to departments)
-   created_at (timestamp)
-   updated_at (timestamp)

### 5. Workflows Table

Fields to add:

-   id (bigint, primary)
-   name (string)
-   department_id (foreign key to departments)
-   type (enum: leave, mission)
-   description (text)
-   is_active (boolean)
-   created_at (timestamp)
-   updated_at (timestamp)

### 6. Workflow Steps Table

Fields to addPos:

-   id (bigint, primary)
-   workflow_id (foreign key to workflows)
-   step_number (integer)
-   role_id (foreign key to roles) - The role that approves at this step
-   approver_id (foreign key to users, optional) - Specific user if assigned
-   description (text)
-   created_at (timestamp)
-   updated_at (timestamp)

### 7. Leave Types Table

New table:

-   id (bigint, primary)
-   name (string) - Annual, Sick, Emergency, etc.
-   max_days (integer)
-   description (text)
-   is_active (boolean)
-   created_at (timestamp)
-   updated_at (timestamp)

### 8. Requests Table (Polymorphic for leave/mission)

New table:

-   id (bigint, primary)
-   user_id (foreign key to users)
-   type (enum: leave, mission)
-   status (enum: pending, approved, rejected, in_progress, completed)
-   workflow_id (foreign key to workflows)
-   current_step_id (foreign key to workflow_steps)
-   data (json) - Flexible field for request-specific data
-   submitted_at (timestamp)
-   decision_at (timestamp)
-   decision_by (foreign key to users)
-   created_at (timestamp)
-   updated_at (timestamp)

### 9. Leave Requests Table (Extends Requests)

New table:

-   id (bigint, primary)
-   request_id (foreign key to requests)
-   leave_type_id (foreign key to leave_types)
-   start_date (date)
-   end_date (date)
-   reason (text)
-   supporting_document (string) - file path
-   created_at (timestamp)
-   updated_at (timestamp)

### 10. Mission Requests Table (Extends Requests)

New table:

-   id (bigint, primary)
-   request_id (foreign key to requests)
-   destination (string)
-   purpose (text)
-   start_date (date)
-   end_date (date)
-   budget (decimal)
-   supporting_document (string) - file path
-   created_at (timestamp)
-   updated_at (timestamp)

### 11. Approvals Table

New table:

-   id (bigint, primary)
-   request_id (foreign key to requests)
-   approver_id (foreign key to users)
-   step_id (foreign key to workflow_steps)
-   status (enum: pending, approved, rejected)
-   comments (text)
-   approved_at (timestamp)
-   created_at (timestamp)
-   updated_at (timestamp)

### 12. Notifications Table

New table:

-   id (bigint, primary)
-   user_id (foreign key to users)
-   type (string) - request_submitted, request_approved, request_rejected, etc.
-   data (json) - Additional data about the notification
-   read_at (timestamp)
-   created_at (timestamp)
-   updated_at (timestamp)

## Relationships

1. Users ↔ Departments (Many-to-One): Users belong to a department
2. Users ↔ Roles (Many-to-Many): Users can have multiple roles through user_roles table
3. Roles ↔ Departments (Many-to-Many): Roles can be specific to departments through user_roles table
4. Departments ↔ Workflows (One-to-Many): Each department can have multiple workflows
5. Workflows ↔ Workflow Steps (One-to-Many): Each workflow has multiple steps
6. Workflow Steps ↔ Roles (Many-to-One): Each step is assigned to a role
7. Users ↔ Requests (One-to-Many): Users can submit multiple requests
8. Requests ↔ Workflows (Many-to-One): Requests follow a specific workflow
9. Requests ↔ Workflow Steps (Many-to-One): Requests are at a current step
10. Requests ↔ Leave Requests (One-to-One): Leave requests extend the base request
11. Requests ↔ Mission Requests (One-to-One): Mission requests extend the base request
12. Users ↔ Approvals (One-to-Many): Users can make multiple approvals
13. Requests ↔ Approvals (One-to-Many): Requests can have multiple approvals
14. Workflow Steps ↔ Approvals (One-to-Many): Steps can have multiple approvals
15. Users ↔ Notifications (One-to-Many): Users can have multiple notifications

## Indexes

-   users.email (unique)
-   departments.name (index)
-   roles.name (index)
-   user_roles.user_id (index)
-   user_roles.role_id (index)
-   user_roles.department_id (index)
-   workflows.department_id (index)
-   workflows.type (index)
-   workflow_steps.workflow_id (index)
-   workflow_steps.step_number (index)
-   requests.user_id (index)
-   requests.type (index)
-   requests.status (index)
-   requests.workflow_id (index)
-   requests.current_step_id (index)
-   approvals.request_id (index)
-   approvals.approver_id (index)
-   notifications.user_id (index)
-   notifications.read_at (index)
