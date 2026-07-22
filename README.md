# Ogun State Political Membership & Election Monitoring System

PHP 8.3 + MySQL 8 MVC starter for membership registration, executive management, polling unit administration, election monitoring, and live result reporting.

## Features

- Native MVC structure
- Role-based access control foundation
- Membership registration flow
- Dashboard and public website pages
- Database schema and starter seed data
- Bootstrap 5 UI with responsive layout
- Prepared for QR/PDF/email extensions

## Installation

1. Import `database/database.sql` into MySQL.
2. Import `database/seed.sql` after the schema.
3. Copy `.env.example` to `.env` and update database credentials.
4. Run `composer install`.
5. Point Apache document root to `public/`.

## Notes

- The project is intentionally scaffolded for safe extension.
- Add real permissions checks in controllers as modules are completed.
- Wire PHPMailer, QR code generation, and PDF generation into dedicated service classes next.
