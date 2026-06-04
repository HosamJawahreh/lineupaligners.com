# Oreo Hospital — Laravel CMS

Hospital management system built with **Laravel 13**, **MySQL**, and the Oreo Hospital Bootstrap admin template.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+

## Setup

1. Configure `.env` database credentials
2. Run migrations and seeders:

```bash
composer install
php artisan migrate --seed
php artisan storage:link
```

3. Download theme images (from official demo):

```bash
bash scripts/download-theme-assets.sh
```

4. Convert HTML templates to Blade (after editing legacy HTML):

```bash
php scripts/convert-to-blade.php
```

5. Start the server:

```bash
php artisan serve
```

## Default login

| Email | Password |
|-------|----------|
| admin@oreo.com | password |

## Views structure

| Path | Description |
|------|-------------|
| `resources/views/layouts/app.blade.php` | Main admin layout |
| `resources/views/layouts/partials/theme-shell.blade.php` | Navbar, sidebar, chat (from original HTML) |
| `resources/views/dashboard/index.blade.php` | Dashboard |
| `resources/views/theme/pages/*.blade.php` | All 59 theme pages converted from HTML |
| `resources/views/auth/login.blade.php` | Login page |
| `template/legacy/*.html` | Original static HTML (reference) |

## Routes

- `/` — Dashboard
- `/doctors`, `/patients`, `/departments`, `/appointments` — Core modules (original theme design)
- `/pages/{slug}` — All other theme pages (blog, mail, UI kit, etc.)

Page registry: `config/theme-pages.php`

## Scripts

- `scripts/download-theme-assets.sh` — Pull images/plugins from wrraptheme demo
- `scripts/convert-to-blade.php` — Regenerate Blade views from `template/legacy/`
