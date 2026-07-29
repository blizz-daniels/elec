# Yayi Youth Vanguard Membership & Polling Unit Marshal Registration

PHP 8.3 + MySQL 8 MVC starter for membership registration, executive management, polling unit administration, marshal coordination, and records reporting.

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
3. Edit `config.php` with your database and domain settings.
4. Run `composer install`.
5. Point Apache document root to `public/`.

## Namecheap / cPanel Deployment

If you are uploading the full project to `public_html`, keep these in mind:

1. Edit the root `config.php` file with your live host values.
2. Upload the project files so the `public/` folder remains inside the app folder.
3. Make sure the domain points to the `public/` folder if your hosting panel allows it.
4. If the app lives in the domain root, the top-level `.htaccess` will forward requests to `public/index.php` and serve `public/assets` and `public/uploads`.
5. Import `database/database.sql` first, then `database/seed.sql`.

## Laragon Test Login

Use the same password for every demo account:

`Test@1234`

| Role | Email |
| --- | --- |
| Super Admin | `superadmin@ogun.test` |
| State Executive | `stateexec@ogun.test` |
| Senatorial Executive | `senatorialexec@ogun.test` |
| LGA Executive | `lgaexec@ogun.test` |
| Ward Executive | `wardexec@ogun.test` |
| Polling Marshal | `marshal@ogun.test` |
| Registered Member | `member@ogun.test` |

## Notes

- The project is intentionally scaffolded for safe extension.
- Add real permissions checks in controllers as modules are completed.
- Wire PHPMailer, QR code generation, and PDF generation into dedicated service classes next.
