# Database Seeding Instructions

This document provides instructions on how to seed your database with realistic test data.

## Created Seeders

We have created the following seeders to generate 50 values for each entity:

1. **BulkUserSeeder** - Creates 50 users with various roles and departments
2. **BulkRequestSeeder** - Creates 50 requests (leave and mission) with realistic data
3. **BulkApprovalSeeder** - Creates 50 approval records
4. **BulkNotificationSeeder** - Creates 50 notification records

## How to Run the Seeders

To seed your database with the new bulk data, run the following command:

```bash
php artisan db:seed
```

This will run all seeders in the order specified in `DatabaseSeeder.php`, including the new bulk seeders.

If you want to run a specific seeder only, you can use:

```bash
php artisan db:seed --class=BulkUserSeeder
php artisan db:seed --class=BulkRequestSeeder
php artisan db:seed --class=BulkApprovalSeeder
php artisan db:seed --class=BulkNotificationSeeder
```

## Seeding Order

The seeders are designed to run in a specific order due to dependencies:

1. DepartmentSeeder
2. RoleSeeder
3. LeaveTypeSeeder
4. UserSeeder
5. WorkflowSeeder
6. RequestSeeder
7. BulkUserSeeder
8. BulkRequestSeeder
9. BulkApprovalSeeder
10. BulkNotificationSeeder

## What Each Seeder Does

### BulkUserSeeder

-   Generates 50 users with realistic names and email addresses
-   Assigns users to random departments
-   Distributes roles based on realistic weights (60% regular users, 15% team leaders, etc.)

### BulkRequestSeeder

-   Creates 50 requests (60% leave, 40% mission)
-   Assigns requests to random users and workflows
-   Generates realistic dates, descriptions, and supporting documents
-   Distributes statuses based on realistic weights

### BulkApprovalSeeder

-   Creates 50 approval records for existing requests
-   Assigns approvers based on required roles for each workflow step
-   Generates realistic approval comments
-   Distributes statuses based on realistic weights

### BulkNotificationSeeder

-   Creates 50 notification records for users
-   Generates various notification types (request submitted, approved, rejected, etc.)
-   Creates realistic notification messages and data
-   Randomly marks some notifications as read

## Resetting and Reseeding

If you need to reset your database and reseed with fresh data, you can use:

```bash
php artisan migrate:fresh --seed
```

This will drop all tables, recreate them, and run all seeders.

## Notes

-   All seeders check for existing data to prevent duplicates
-   Password for all users is set to "password" for testing purposes
-   The seeders generate realistic data distributions to simulate a real-world application
-   You can modify the weights and data generation logic in each seeder to better fit your specific needs
