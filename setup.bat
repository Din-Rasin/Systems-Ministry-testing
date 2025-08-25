@echo off
title Laravel Workflow Management System Setup

echo 🚀 Starting Laravel Workflow Management System Setup...
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Error: This script must be run from the Laravel project root directory
    pause
    exit /b 1
)

echo ✅ Laravel project detected
echo.

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
composer install
echo.

REM Check if .env file exists
if not exist ".env" (
    echo 📋 Creating .env file from .env.example...
    copy .env.example .env
)
echo.

REM Generate application key
echo 🔑 Generating application key...
php artisan key:generate
echo.

REM Check database configuration
echo 🔍 Checking database configuration...
findstr /C:"DB_DATABASE=laravel" .env >nul
if %errorlevel% == 0 (
    echo ⚠️  Warning: Default database name detected. Please update .env with your database credentials.
)
echo.

REM Run migrations
echo 🏗️  Running database migrations...
php artisan migrate
echo.

REM Check if we want to seed the database
echo 🌱 Do you want to seed the database with sample data? (y/n)
set /p seed_choice=
if /i "%seed_choice%" == "y" (
    echo 🌱 Seeding database...
    php artisan db:seed
)
echo.

REM Setup React frontend
echo ⚛️  Setting up React frontend...
if exist "react-frontend" (
    cd react-frontend
    echo 📦 Installing React dependencies...
    npm install
    cd ..
) else (
    echo ⚠️  Warning: React frontend directory not found
)
echo.

echo ✅ Setup completed successfully!
echo.
echo 📖 To run the application:
echo 1. Start the Laravel backend: php artisan serve
echo 2. In another terminal, start the React frontend: cd react-frontend && npm run dev
echo 3. Visit http://localhost:8000 for backend or http://localhost:5173 for frontend
echo.
echo 🔐 Default login credentials (if seeded):
echo    Email: admin@example.com
echo    Password: password
echo.
pause
