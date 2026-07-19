# Phase 1 Deployment Notes

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

For local development:

```bash
composer run dev
```

## Environment

Required production values:

```dotenv
APP_NAME="Condo Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=condo_app
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
SANCTUM_STATEFUL_DOMAINS=your-domain.example
```

Run `php artisan storage:link` when facility images are enabled.

## Seeded Development Accounts

All seeded passwords are development-only: `password`.

- `superadmin@example.com`
- `admin@example.com`
- `guard@example.com`
- `resident@example.com`
- `pending@example.com`
- `rejected@example.com`

Seeded property structure:

- Property: Broadleaf Residence
- Block: 12A
- Unit: 184G

## Commands

```bash
php artisan test
./vendor/bin/pint
npm run build
php artisan visitors:expire
```

Schedule `php artisan schedule:run` every minute in production so expired visitor passes can be processed when scheduled commands are registered.

## Deployment Checklist

- Set `APP_DEBUG=false`.
- Configure trusted production `APP_URL` and Sanctum domains.
- Run migrations before enabling traffic.
- Run `php artisan config:cache`, `route:cache`, and `view:cache`.
- Start queue workers if queued notifications or mail are enabled.
- Configure HTTPS at the load balancer or web server.
- Verify camera permissions on the guard scanner over HTTPS.
- Confirm MySQL backups and audit-log retention.
- Confirm no secrets are present in frontend environment variables.

## Known Phase 1 Limitations

- Visitor QR codes are single-entry only by design.
- QR content is a secure token only; no visitor personal details are stored inside the QR.
- Payments, deposits, refunds, and number-plate scanning are intentionally excluded.
- Email notifications are abstracted for future work but the MVP uses in-app notifications.
- Selected-resident notification drafts should be published immediately if exact recipient selection must be preserved.

## Recommended Phase 2

- Add Vitest coverage for Pinia stores and critical forms.
- Add queue-backed email/SMS notifications.
- Add richer calendar views for facility operations.
- Add resident profile change approval workflows for unit/property changes.
- Add notification scheduling and recipient snapshots for complex drafts.
- Add audit-log retention pruning and export controls.
- Add formal browser tests for mobile guard scanning.
