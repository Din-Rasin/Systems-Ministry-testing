#!/bin/bash

# Laravel Workflow Management System Setup Script

echo "🚀 Starting Laravel Workflow Management System Setup..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: This script must be run from the Laravel project root directory"
    exit 1
fi

echo "✅ Laravel project detected"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "📋 Creating .env file from .env.example..."
    cp .env.example .env
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Check database configuration
echo "🔍 Checking database configuration..."
if grep -q "DB_DATABASE=laravel" .env; then
    echo "⚠️  Warning: Default database name detected. Please update .env with your database credentials."
fi

# Run migrations
echo "🏗️  Running database migrations..."
php artisan migrate

# Check if we want to seed the database
echo "🌱 Do you want to seed the database with sample data? (y/n)"
read -r seed_choice
if [[ $seed_choice == "y" || $seed_choice == "Y" ]]; then
    echo "🌱 Seeding database..."
    php artisan db:seed
fi

# Setup React frontend
echo "⚛️  Setting up React frontend..."
if [ -d "react-frontend" ]; then
    cd react-frontend
    echo "📦 Installing React dependencies..."
    npm install
    cd ..
else
    echo "⚠️  Warning: React frontend directory not found"
fi

echo "✅ Setup completed successfully!"

echo " "
echo "📖 To run the application:"
echo "1. Start the Laravel backend: php artisan serve"
echo "2. In another terminal, start the React frontend: cd react-frontend && npm run dev"
echo "3. Visit http://localhost:8000 for backend or http://localhost:5173 for frontend"
echo " "
echo "🔐 Default login credentials (if seeded):"
echo "   Email: admin@example.com"
echo "   Password: password"
