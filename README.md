<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Laravel Workflow Management System

A comprehensive workflow management system built with Laravel for handling leave and mission requests with department-specific approval workflows.

## 🚀 Features

### Core Functionality
- **Multi-Department Support**: IT Department and Sales Department with custom workflows
- **Request Management**: Submit, track, and manage leave and mission requests
- **Approval Workflows**: Automated multi-step approval processes
- **Role-Based Access Control**: Different permissions for employees, team leaders, managers, and administrators
- **Real-time Dashboard**: Statistics, charts, and pending items overview
- **Responsive Design**: Modern, mobile-friendly interface built with Bootstrap 5

### User Roles & Permissions

#### Employee
- Submit leave and mission requests
- View own request status and history
- Track approval progress

#### Team Leader
- All employee permissions
- Approve team member requests (first step in workflows)

#### HR Manager
- All employee permissions
- Approve leave requests (final step for most departments)
- View all requests across departments

#### CFO
- All employee permissions
- Approve financial aspects of requests
- Required for Sales department workflows

#### CEO
- All employee permissions
- Final approval for mission requests
- Executive oversight of all processes

#### Department Administrator
- Manage department workflows
- Submit requests on behalf of department

#### System Administrator
- Full system access
- User management
- Role assignment
- Department allocation

### Department Workflows

#### IT Department
- **Leave Requests**: Team Leader → HR Manager
- **Mission Requests**: Team Leader → CEO

#### Sales Department
- **Leave Requests**: Team Leader → CFO → HR Manager
- **Mission Requests**: Team Leader → CFO → HR Manager → CEO

## 🛠 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- SQLite (default) or MySQL
- Node.js & NPM (for frontend assets)

### Step 1: Clone & Install Dependencies
```bash
git clone <repository-url>
cd workflow-management-system
composer install
npm install
```

### Step 2: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Database Setup
```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations and seed demo data
php artisan migrate --seed
```

### Step 4: Compile Assets
```bash
npm run build
```

### Step 5: Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 👥 Demo Users

The system comes with pre-configured demo users for testing:

| Role | Email | Password | Department |
|------|--------|----------|------------|
| System Admin | admin@company.com | password | - |
| CEO | ceo@company.com | password | - |
| CFO | cfo@company.com | password | Finance |
| HR Manager | hr@company.com | password | HR |
| IT Team Leader | it-leader@company.com | password | IT |
| IT Employee | charlie@company.com | password | IT |
| Sales Team Leader | sales-leader@company.com | password | Sales |
| Sales Employee | eve@company.com | password | Sales |
| IT Dept Admin | it-admin@company.com | password | IT |
| Sales Dept Admin | sales-admin@company.com | password | Sales |

## 📋 Usage Guide

### For Employees

1. **Login** with your credentials
2. **Submit Requests**:
   - Navigate to Dashboard
   - Click "Submit Leave Request" or "Submit Mission Request"
   - Fill out the form with required details
   - Submit for approval

3. **Track Requests**:
   - View "My Requests" to see all submitted requests
   - Click on any request to see detailed approval progress
   - Monitor workflow steps and approver comments

### For Approvers

1. **Review Pending Approvals**:
   - Check "Pending Approvals" in sidebar (shows count)
   - Review request details and supporting information
   - Add comments and approve or reject

2. **Bulk Operations**:
   - Select multiple requests for bulk approval
   - Add common comments for efficiency

### For Administrators

1. **User Management** (System Admin):
   - Navigate to Admin → Manage Users
   - Create new users and assign roles
   - Allocate users to departments

2. **Workflow Management**:
   - Navigate to Admin → Manage Workflows
   - Modify approval steps and sequences
   - Activate/deactivate workflows

## 🏗 Architecture

### Database Schema
- **Users**: Employee information and authentication
- **Departments**: Organizational units with managers
- **Roles**: Permission-based access control
- **Workflows**: Department-specific approval processes
- **Workflow Steps**: Individual approval stages
- **Requests**: Leave and mission submissions
- **Request Approvals**: Tracking approval status per step

### Key Components

#### Models
- `User`: Authentication and role management
- `Department`: Organizational structure
- `Role`: Permission system
- `Workflow`: Approval process definition
- `WorkflowStep`: Individual approval stages
- `Request`: Leave/mission submissions
- `RequestApproval`: Approval tracking

#### Services
- `WorkflowService`: Core business logic for request processing
- Handles submission, approval routing, and status management

#### Controllers
- `AuthController`: Login/logout functionality
- `DashboardController`: Statistics and overview
- `RequestController`: CRUD operations for requests
- `ApprovalController`: Approval management
- `Admin/UserController`: User administration
- `Admin/WorkflowController`: Workflow management

## 🎨 Frontend Features

### Dashboard
- Real-time statistics cards
- Recent requests overview
- Pending approvals summary
- Interactive charts and graphs
- Quick action buttons

### Request Management
- Intuitive form design
- Date validation and conflict checking
- File attachment support
- Progress tracking visualization
- Status badges and indicators

### Approval Interface
- Clean approval review screens
- Workflow step visualization
- Comment system for feedback
- Bulk approval capabilities
- Email notifications (planned)

## 🔧 Technical Details

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.4)
- **Database**: SQLite (configurable to MySQL)
- **Frontend**: Blade templates with Bootstrap 5
- **Icons**: Bootstrap Icons
- **Charts**: Chart.js
- **Authentication**: Laravel's built-in system

### Security Features
- CSRF protection on all forms
- Role-based route protection
- Input validation and sanitization
- SQL injection prevention
- XSS protection

### Performance Optimizations
- Eager loading of relationships
- Database query optimization
- Efficient pagination
- Real-time updates via AJAX
- Responsive design for mobile devices

## 🚧 Future Enhancements

### Planned Features
- Email notifications for status changes
- PDF export for requests
- Advanced reporting and analytics
- Mobile app development
- Integration with HR systems
- Calendar integration for leave planning
- Automated reminders for pending approvals

### Potential Improvements
- Multi-language support
- Advanced search and filtering
- Audit trail for all actions
- Custom workflow builder UI
- API for third-party integrations

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests for new functionality
5. Ensure code follows PSR standards
6. Submit a pull request

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the development team
- Check the documentation for common solutions

---

**Built with ❤️ using Laravel Framework**
