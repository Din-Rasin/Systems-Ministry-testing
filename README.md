# Workflow Demo (Laravel)

## Quick start

1. Create environment

```bash
cp .env .env.local 2>/dev/null || true
# or ensure .env exists with sqlite
```

2. Install PHP deps (requires PHP 8.2+) and Node deps

```bash
composer install
npm install
```

3. Generate key, migrate and seed

```bash
php artisan key:generate
php artisan migrate --seed
```

4. Run

```bash
php artisan serve
npm run dev
```

## Demo users

- Admin: admin@example.com / password
- User: user@example.com / password
- Approver: approver@example.com / password

## Features

- Department-specific workflows for leave and mission
- Multi-step approvals per workflow
- Users can submit and track requests
- Approvers can approve/reject in sequence
- Admins can define workflows and assign to departments
