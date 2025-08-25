# Laravel Workflow Management System - Detailed Flowchart

## Complete System Flow Based on Requirements

```mermaid
graph TD
    %% User Authentication & Role Assignment
    A[System Start] --> B[User Login]
    B --> C{Authentication Valid?}
    C -->|No| D[Login Error]
    C -->|Yes| E[Load User Profile]
    D --> B
    E --> F{User Role?}

    %% Role-based Dashboard Routing
    F -->|Employee| G[Employee Dashboard]
    F -->|Team Leader| H[Team Leader Dashboard]
    F -->|HR Manager| I[HR Manager Dashboard]
    F -->|CFO| J[CFO Dashboard]
    F -->|CEO| K[CEO Dashboard]
    F -->|Department Admin| L[Department Admin Dashboard]
    F -->|System Admin| M[System Admin Dashboard]

    %% Employee Functions
    G --> N{Action Choice}
    N -->|Submit Request| O[Create New Request]
    N -->|Check Status| P[View My Requests]

    %% Request Creation Process
    O --> Q{Request Type}
    Q -->|Leave Request| R[Leave Request Form]
    Q -->|Mission Request| S[Mission Request Form]

    R --> T[Enter Leave Details]
    T --> U[Select Leave Type]
    U --> V[Choose Dates]
    V --> W[Check Leave Balance]
    W --> X{Balance Available?}
    X -->|No| Y[Show Balance Error]
    X -->|Yes| Z[Add Reason & Documents]
    Y --> R
    Z --> AA[Submit Leave Request]

    S --> BB[Enter Mission Details]
    BB --> CC[Set Destination]
    CC --> DD[Define Purpose]
    DD --> EE[Set Travel Period]
    EE --> FF[Estimate Budget]
    FF --> GG[Upload Documents]
    GG --> HH[Submit Mission Request]

    %% Workflow Routing Engine
    AA --> II[Workflow Engine]
    HH --> II
    II --> JJ{User Department?}
    JJ -->|IT Department| KK[IT Workflow Logic]
    JJ -->|Sales Department| LL[Sales Workflow Logic]

    %% IT Department Workflows
    KK --> MM{IT Request Type?}
    MM -->|Leave| NN[IT Leave: Team Leader → HR Manager]
    MM -->|Mission| OO[IT Mission: Team Leader → CEO]

    %% Sales Department Workflows
    LL --> PP{Sales Request Type?}
    PP -->|Leave| QQ[Sales Leave: Team Leader → CFO → HR Manager]
    PP -->|Mission| RR[Sales Mission: Team Leader → CFO → HR Manager → CEO]

    %% Approval Process
    NN --> SS[Create Approval Chain]
    OO --> SS
    QQ --> SS
    RR --> SS
    SS --> TT[Send to First Approver]
    TT --> UU[Approver Notification]

    %% Team Leader Dashboard Functions
    H --> VV{Team Leader Actions}
    VV -->|Review Requests| WW[View Team Requests]
    VV -->|Submit Own Request| O

    %% Approval Decision Process
    WW --> XX[Select Request to Review]
    XX --> YY[Review Request Details]
    YY --> ZZ[Check Supporting Documents]
    ZZ --> AAA{Approval Decision?}
    AAA -->|Approve| BBB[Add Approval Comments]
    AAA -->|Reject| CCC[Add Rejection Reason]
    AAA -->|Need Info| DDD[Request More Information]

    BBB --> EEE[Record Approval]
    CCC --> FFF[Record Rejection]
    DDD --> GGG[Send Info Request]

    EEE --> HHH{More Approval Steps?}
    HHH -->|Yes| III[Send to Next Approver]
    HHH -->|No| JJJ[Mark as Fully Approved]
    III --> UU

    FFF --> KKK[Notify Requester - Rejected]
    JJJ --> LLL[Notify Requester - Approved]
    GGG --> MMM[Employee Provides Info]
    MMM --> XX

    %% HR Manager Functions
    I --> NNN{HR Manager Actions}
    NNN -->|Process Leave Approvals| OOO[HR Leave Approvals]
    NNN -->|Manage Leave Balances| PPP[Leave Balance Management]
    NNN -->|Submit Own Request| O

    OOO --> XX
    PPP --> QQQ[Update Employee Leave Balances]

    %% CFO Functions
    J --> RRR{CFO Actions}
    RRR -->|Review Budget Requests| SSS[Mission Budget Approvals]
    RRR -->|Financial Oversight| TTT[Budget Analysis]
    RRR -->|Submit Own Request| O

    SSS --> XX

    %% CEO Functions
    K --> UUU{CEO Actions}
    UUU -->|Final Approvals| VVV[CEO Final Review]
    UUU -->|Executive Oversight| WWW[Company Analytics]
    UUU -->|Submit Own Request| O

    VVV --> XX

    %% Department Admin Functions
    L --> XXX{Department Admin Actions}
    XXX -->|Manage Department Workflows| YYY[Configure Department Workflows]
    XXX -->|Manage Department Users| ZZZ[Department User Management]
    XXX -->|Submit Own Request| O

    YYY --> AAAA[Select Department]
    AAAA --> BBBB[Define Approval Chain]
    BBBB --> CCCC[Set Approval Roles]
    CCCC --> DDDD[Configure Workflow Rules]
    DDDD --> EEEE[Save Workflow Configuration]

    ZZZ --> FFFF[View Department Users]
    FFFF --> GGGG[Assign User Roles]
    GGGG --> HHHH[Update User Departments]

    %% System Admin Functions
    M --> IIII{System Admin Actions}
    IIII -->|User Management| JJJJ[Create/Manage Users]
    IIII -->|Department Management| KKKK[Create/Manage Departments]
    IIII -->|System Configuration| LLLL[Global System Settings]
    IIII -->|Submit Own Request| O

    JJJJ --> MMMM[Create New User]
    MMMM --> NNNN[Set User Role]
    NNNN --> OOOO[Assign to Department]
    OOOO --> PPPP[Activate User Account]

    KKKK --> QQQQ[Create Department]
    QQQQ --> RRRR[Assign Department Admin]
    RRRR --> SSSS[Configure Department Settings]

    %% Final Processing
    LLL --> TTTT[Update Leave Balance]
    TTTT --> UUUU[Generate Approval Certificate]
    UUUU --> VVVV[Archive Request]
    VVVV --> WWWW[Update Statistics]

    KKK --> XXXX[Update Request Status]
    XXXX --> WWWW
    WWWW --> YYYY[End Process]

    %% Individual Node Styling
    style A fill:#e1f5fe
    style B fill:#e1f5fe
    style G fill:#f3e5f5
    style H fill:#f3e5f5
    style I fill:#f3e5f5
    style J fill:#f3e5f5
    style K fill:#f3e5f5
    style L fill:#f3e5f5
    style M fill:#f3e5f5
    style AA fill:#e8f5e8
    style HH fill:#e8f5e8
    style JJJ fill:#e8f5e8
    style LLL fill:#e8f5e8
    style EEEE fill:#e8f5e8
    style PPPP fill:#e8f5e8
    style SSSS fill:#e8f5e8
    style YYYY fill:#e8f5e8
    style C fill:#fff3e0
    style F fill:#fff3e0
    style N fill:#fff3e0
    style Q fill:#fff3e0
    style X fill:#fff3e0
    style JJ fill:#fff3e0
    style MM fill:#fff3e0
    style PP fill:#fff3e0
    style AAA fill:#fff3e0
    style HHH fill:#fff3e0
    style NNN fill:#fff3e0
    style RRR fill:#fff3e0
    style UUU fill:#fff3e0
    style XXX fill:#fff3e0
    style IIII fill:#fff3e0
    style D fill:#ffebee
    style Y fill:#ffebee
    style CCC fill:#ffebee
    style FFF fill:#ffebee
    style KKK fill:#ffebee
```

## Key Features of This Flowchart

This flowchart accurately represents the Laravel Workflow Management System with:

1. **User Authentication & Role Assignment** - Multi-role authentication system with proper routing to role-specific dashboards
2. **Request Management** - Separate workflows for Leave and Mission requests with detailed form processes
3. **Department-Specific Workflows** - IT and Sales departments with their specific approval chains
4. **Multi-Level Approval Processes** - Sequential approval handling with different paths for each department and request type
5. **Role-Specific Functions** - Each role (Team Leader, HR Manager, CFO, CEO, Department Admin, System Admin) has specific actions they can perform
6. **Notification System** - Integrated notification process at each stage
7. **Final Processing** - Complete request closure with leave balance updates and certificate generation

The flowchart maintains the exact structure and styling from your original diagram while incorporating the specific requirements of the Laravel Workflow Management System as documented in the project files.
