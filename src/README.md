# Typhon Cath CRM

A modular PHP/MySQL CRM project for Typhon Cath.

## Project Structure

This repo uses a modular PHP monolith structure:

- `public/` is the only web-accessible directory.
- `app/Core/` contains shared application logic.
- `app/Modules/` contains student-owned CRM modules.
- `config/` contains app/database configuration.
- `database/` contains schema, seed data, indexes, and migrations.
- `docs/` contains FP documentation and diagrams.
- `storage/` contains logs and backups.

## Student Ownership

| Student | Module | Folder |
|---|---|---|
| Max | Customer Management | `app/Modules/Customer/` |
| Trevor | RFQ / Pipeline Management | `app/Modules/RFQ/` |
| Jonah | Digital Campaign Management | `app/Modules/Campaign/` |
| Casey | Inventory Management | `app/Modules/Inventory/` |
| All | Dashboard, Admin, Integration, Auth | `app/Modules/Dashboard/`, `app/Modules/Admin/`, `app/Core/` |

## Basic Request Flow

Example: creating an RFQ.

```text
User submits Create RFQ form
        ↓
RFQController.php receives request
        ↓
RFQService.php validates business rules
        ↓
RFQRepository.php inserts/updates MySQL
        ↓
DashboardService.php can read updated metrics
        ↓
User redirects to RFQ detail or pipeline board
```

## Setup Notes

1. Copy `.env.example` to `.env`.
2. Create a MySQL database.
3. Import `database/schema.sql`.
4. Import `database/seed.sql`.
5. Configure your local web server so `/public` is the document root.
6. Visit `/login.php`.

## Security Notes

- Do not expose `/app`, `/config`, `/database`, `/storage`, or `/docs` through the web server.
- Use password hashing for user passwords.
- Use sessions and role-based access checks for protected pages.
- Use prepared statements for SQL queries.
