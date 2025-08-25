# Laravel Workflow Management System - Setup Guide

This guide will help you set up and run the complete Laravel Workflow Management System with both backend and frontend components.

## Prerequisites

Before you begin, ensure you have the following installed:

-   PHP 8.1 or higher
-   Composer
-   Node.js 16+ and npm
-   MySQL or another supported database
-   XAMPP/WAMP/MAMP or any local server environment

## Backend Setup (Laravel)

### 1. Install PHP Dependencies

Navigate to the project root directory and run:

```bash
composer install
```

### 2. Configure Environment

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Update the database configuration in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Database Migrations

```bash
php artisan migrate
```

### 5. Seed the Database (Optional)

To populate the database with sample data:

```bash
php artisan db:seed
```

### 6. Start the Development Server

```bash
php artisan serve
```

The backend will be available at `http://localhost:8000`

## Frontend Setup (React)

### 1. Install Node Dependencies

Navigate to the `react-frontend` directory:

```bash
cd react-frontend
npm install
```

### 2. Configure API Endpoint

The frontend is already configured to connect to the Laravel backend at `http://localhost:8000`. If you need to change this, update the `baseURL` in `react-frontend/src/services/api.js`.

### 3. Start the Development Server

```bash
npm run dev
```

The frontend will be available at `http://localhost:5173`

## Running Both Servers Together

To run both the backend and frontend simultaneously:

1. In one terminal, start the Laravel backend:

    ```bash
    php artisan serve
    ```

2. In another terminal, start the React frontend:
    ```bash
    cd react-frontend
    npm run dev
    ```

## Database Configuration

### Creating the Database

1. Create a new MySQL database:

    ```sql
    CREATE DATABASE laravel_workflow;
    ```

2. Update your `.env` file with the database credentials:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=laravel_workflow
    DB_USERNAME=your_mysql_username
    DB_PASSWORD=your_mysql_password
    ```

### Running Migrations

After configuring the database, run:

```bash
php artisan migrate
```

This will create all necessary tables for the workflow system.

## Authentication Setup

The system uses Laravel's built-in authentication. To enable user registration and login:

1. Ensure the database is set up and migrated
2. Run the development server:
    ```bash
    php artisan serve
    ```
3. Visit `http://localhost:8000` to access the login page

## API Endpoints

The Laravel backend provides the following API endpoints:

-   `GET /api/requests` - Get all requests
-   `POST /api/requests/leave` - Create a leave request
-   `POST /api/requests/mission` - Create a mission request
-   `GET /api/approvals/pending` - Get pending approvals
-   `POST /api/approvals/{id}` - Process an approval

## Troubleshooting

### Common Issues

1. **Database Connection Error**

    - Check your database credentials in `.env`
    - Ensure MySQL is running
    - Verify the database exists

2. **CORS Error**

    - Install the Laravel CORS package:
        ```bash
        composer require fruitcake/laravel-cors
        ```

3. **Missing Storage Link**

    - Create a symbolic link for storage:
        ```bash
        php artisan storage:link
        ```

4. **Permission Issues**
    - Ensure proper file permissions:
        ```bash
        chmod -R 755 storage bootstrap/cache
        ```

### Clearing Cache

If you encounter issues, try clearing the cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Development Workflow

### Backend Development

1. Modify controllers in `app/Http/Controllers/`
2. Update models in `app/Models/`
3. Create new migrations if needed:
    ```bash
    php artisan make:migration create_table_name
    ```

### Frontend Development

1. Modify components in `react-frontend/src/components/`
2. Update pages in `react-frontend/src/pages/`
3. Add new hooks in `react-frontend/src/hooks/`
4. Update services in `react-frontend/src/services/`

## Deployment

### Backend Deployment

1. Upload all files to your server
2. Run composer install:
    ```bash
    composer install --no-dev
    ```
3. Set proper file permissions
4. Configure your web server (Apache/Nginx) to point to the `public` directory

### Frontend Deployment

1. Build the React app:
    ```bash
    npm run build
    ```
2. Upload the contents of `dist` to your web server

## Testing

### Backend Testing

Run PHPUnit tests:

```bash
php artisan test
```

### Frontend Testing

Run React tests:

```bash
npm test
```

## Additional Configuration

### Email Configuration

Update your `.env` file with your email settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration

To process background jobs, start the queue worker:

```bash
php artisan queue:work
```

## Support

For additional help, refer to:

-   Laravel Documentation: https://laravel.com/docs
-   React Documentation: https://reactjs.org/docs/getting-started.html
-   Mermaid Documentation: https://mermaid-js.github.io/mermaid/

## License

This project is licensed under the MIT License.
