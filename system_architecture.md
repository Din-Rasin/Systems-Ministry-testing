# Laravel Workflow Management System - System Architecture

## Overview

This document provides a visual representation of the system architecture for the Laravel Workflow Management System using Mermaid diagrams.

For a more detailed and comprehensive flowchart of the entire system, please refer to the [Workflow Flowchart](workflow_flowchart.md).

## System Components Architecture

```mermaid
graph TD
    A[Client Layer] --> B[Web Server]
    A --> C[API Layer]
    A --> D[Mobile App]

    B --> E[Laravel Application]
    C --> E
    D --> C

    E --> F[Authentication System]
    E --> G[Authorization System]
    E --> H[Workflow Engine]
    E --> I[Request Management]
    E --> J[User Management]
    E --> K[Dashboard & Reporting]

    F --> L[Login/Logout]
    F --> M[Registration]
    F --> N[Password Reset]

    G --> O[Role-based Access]
    G --> P[Department-based Access]
    G --> Q[Gates & Policies]

    H --> R[Workflow Definition]
    H --> S[Approval Process]
    H --> T[Workflow Execution]

    I --> U[Leave Requests]
    I --> V[Mission Requests]
    I --> W[Request Processing]

    J --> X[User Profiles]
    J --> Y[Role Assignment]
    J --> Z[Department Allocation]

    K --> AA[Statistics Dashboard]
    K --> AB[Reporting Engine]
    K --> AC[Analytics]

    E --> AD[Database Layer]
    AD --> AE[MySQL/PostgreSQL]

    E --> AF[File Storage]
    AF --> AG[Local Storage]
    AF --> AH[Cloud Storage]

    E --> AI[Cache Layer]
    AI --> AJ[Redis/Memcached]

    E --> AK[Queue System]
    AK --> AL[Background Jobs]
    AK --> AM[Notifications]

    E --> AN[Email System]
    AN --> AO[SMTP]
    AN --> AP[Email Templates]
```

## Database Schema Diagram

```mermaid
erDiagram
    USERS ||--o{ USER_ROLES : has
    USERS ||--o{ REQUESTS : submits
    USERS ||--o{ APPROVALS : makes
    USERS ||--o{ NOTIFICATIONS : receives

    DEPARTMENTS ||--o{ USER_ROLES : contains
    DEPARTMENTS ||--o{ WORKFLOWS : defines

    ROLES ||--o{ USER_ROLES : assigned
    ROLES ||--o{ WORKFLOW_STEPS : approves

    USER_ROLES }|--|| USERS : belongs
    USER_ROLES }|--|| ROLES : has
    USER_ROLES }|--|| DEPARTMENTS : in

    WORKFLOWS ||--o{ WORKFLOW_STEPS : contains
    WORKFLOWS ||--o{ REQUESTS : processes

    WORKFLOW_STEPS }|--|| WORKFLOWS : belongs
    WORKFLOW_STEPS }|--|| ROLES : requires
    WORKFLOW_STEPS ||--o{ APPROVALS : generates

    REQUESTS ||--|| LEAVE_REQUESTS : extends
    REQUESTS ||--|| MISSION_REQUESTS : extends
    REQUESTS }|--|| USERS : submitted_by
    REQUESTS }|--|| WORKFLOWS : follows
    REQUESTS }|--o{ APPROVALS : requires
    REQUESTS }|--o{ NOTIFICATIONS : generates

    LEAVE_TYPES ||--o{ LEAVE_REQUESTS : categorizes

    APPROVALS }|--|| REQUESTS : for
    APPROVALS }|--|| USERS : approved_by
    APPROVALS }|--|| WORKFLOW_STEPS : at_step

    NOTIFICATIONS }|--|| USERS : for_user
```

## Workflow Process Flow

```mermaid
flowchart TD
    A[User Submits Request] --> B{Request Type}
    B -->|Leave| C[Leave Request Form]
    B -->|Mission| D[Mission Request Form]
    C --> E[Validate Input]
    D --> E
    E --> F[Assign Workflow]
    F --> G[Set Initial Status]
    G --> H[Send Notification]
    H --> I[Route to First Approver]
    I --> J{Approval Decision}
    J -->|Approve| K[Move to Next Step]
    J -->|Reject| L[End Workflow - Rejected]
    K --> M{More Steps?}
    M -->|Yes| N[Route to Next Approver]
    M -->|No| O[Complete Request]
    N --> J
    O --> P[Send Completion Notification]
    L --> P
```

## Authentication & Authorization Flow

```mermaid
flowchart TD
    A[User Accesses System] --> B[Login Page]
    B --> C[Enter Credentials]
    C --> D{Valid Credentials?}
    D -->|No| E[Show Error]
    E --> B
    D -->|Yes| F[Create Session]
    F --> G[Load User Roles]
    G --> H[Load Department Info]
    H --> I[Redirect to Dashboard]
    I --> J{Role-based Routing}
    J -->|User| K[User Dashboard]
    J -->|Team Leader| L[Team Leader Dashboard]
    J -->|HR Manager| M[HR Dashboard]
    J -->|CFO| N[CFO Dashboard]
    J -->|CEO| O[CEO Dashboard]
    J -->|Admin| P[Admin Dashboard]

    K --> Q[Check Permissions]
    L --> Q
    M --> Q
    N --> Q
    O --> Q
    P --> Q
    Q --> R{Authorized Access?}
    R -->|Yes| S[Allow Action]
    R -->|No| T[Show Access Denied]
```

## Notification System Architecture

```mermaid
graph TD
    A[Event Triggered] --> B[Event Listener]
    B --> C{Notification Type}
    C -->|Email| D[Email Job Queue]
    C -->|Database| E[Database Notification]
    C -->|Real-time| F[WebSocket Broadcast]

    D --> G[Email Service]
    G --> H[Send Email]

    E --> I[Notifications Table]
    I --> J[User Notification Center]

    F --> K[WebSocket Server]
    K --> L[Push to Connected Clients]
```

## File Management System

```mermaid
graph TD
    A[File Upload Request] --> B[File Validation]
    B --> C{Valid File?}
    C -->|No| D[Return Error]
    C -->|Yes| E[File Sanitization]
    E --> F[ virus Scanning]
    F --> G{Threat Detected?}
    G -->|Yes| H[Reject File]
    G -->|No| I[Store File]
    I --> J[Generate File Reference]
    J --> K[Save to Database]
    K --> L[Return Success Response]
```
