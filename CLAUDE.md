# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Online photography gallery for a photographer. Public-facing gallery with admin-only backend for managing albums and photos.

## Commands

```bash
# Install PHP dependencies
composer install

# Run the dev server (choose one)
symfony serve
php -S localhost:8000 -t public/

# Database: create schema via migrations (never doctrine:schema:update)
php bin/console doctrine:migrations:migrate

# Generate a new migration after entity changes
php bin/console make:migration

# Dev workflow for entity changes (see Development Preferences): drop and recreate,
# don't chain incremental ALTER migrations
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Create an admin user (only way to get a login; no public registration)
php bin/console app:create-admin admin@example.com somepassword

# Run tests (PHPUnit 11, config in phpunit.dist.xml)
php bin/phpunit
php bin/phpunit tests/Path/To/SomeTest.php
php bin/phpunit --filter testMethodName

# Install/refresh importmap-managed JS assets after adding one to importmap.php
php bin/console importmap:install

# List all registered routes (routes are attribute-based, see config/routes.yaml)
php bin/console debug:router
```

## Tech Stack
- **Backend:** PHP 8.2+, Symfony 7.4
- **Database:** MariaDB 10.4.32 (XAMPP), database name: `CrgPhotography`
- **Frontend:** Twig + Bootstrap 5 (via AssetMapper/importmap) + Stimulus (no Turbo — removed on purpose; plain full-page navigation)
- **Upload:** VichUploaderBundle (installed, not yet configured)
- **Asset system:** AssetMapper (not Webpack/Encore)
- **Server:** XAMPP local, `php -S localhost:8000 -t public/` or `symfony serve`

## Database Config
- `DATABASE_URL` is in `.env.local` (not committed to git)
- Connection: `mysql://root:@127.0.0.1:3306/CrgPhotography?serverVersion=10.4.32-MariaDB`
- `APP_SECRET` is also in `.env.local`

## Entities

### User (`src/Entity/User.php`)
- Standard Symfony security user (email + password + roles)
- Used for admin login only (no public registration)
- Referenced by Album and Photo as `created_by` / `updated_by`

### Album (`src/Entity/Album.php`)
Columns in order: `id`, `title`, `description`, `cover_id`, `sort_order`, `visible`, `created_at`, `created_by`, `updated_at`, `updated_by`
- `cover_id` → ManyToOne to Photo (nullable, ON DELETE SET NULL)
- `created_by` / `updated_by` → ManyToOne to User
- `visible` → boolean (draft/published)
- `photos` → OneToMany to Photo (cascade remove, ordered by sortOrder ASC)

### Photo (`src/Entity/Photo.php`)
Columns in order: `id`, `album_id`, `path`, `title`, `description`, `sort_order`, `visible`, `created_at`, `created_by`, `updated_at`, `updated_by`
- `album_id` → ManyToOne to Album (not nullable)
- `path` → file path on disk (VARCHAR 500)
- `title`, `description` → optional
- `visible` → boolean (show/hide individual photo)
- `created_by` / `updated_by` → ManyToOne to User

## Column Naming Conventions
- Foreign keys to User: `created_by`, `updated_by` (not `created_by_id`)
- Foreign key for cover: `cover_id`
- Foreign key for album: `album_id`
- Order field: `sort_order` (property: `sortOrder`, avoids SQL reserved word `order`)

## GitHub
- Repository: `https://github.com/ionutvalentin99/CrgPhotography`
- Branch: `main`
- `.env.local` and `.idea/` are in `.gitignore`

## Architecture Notes

### Routing
No central route files per feature — routes are defined via `#[Route]` attributes directly on controllers and auto-imported through `config/routes.yaml` (the `controllers: resource: routing.controllers` import). Admin controllers live under `src/Controller/Admin/` with a class-level `#[Route('/admin')]` prefix (see `DashboardController`); new admin controllers should follow this pattern and sit behind the `^/admin` entry in `access_control` (`config/packages/security.yaml`).

### Auth flow
Custom form-login authentication (`src/Security/Authenticator.php`, a Symfony `AbstractLoginFormAuthenticator`), not the `security:form_login` shortcut. It authenticates by `email` against `App\Entity\User` via `app_user_provider` (entity provider keyed on `email`), supports remember-me, and redirects to `admin_dashboard` on success (or to the originally requested target path). `SecurityController` (`src/Controller/SecurityController.php`) only renders the login form and defines the `app_logout` route — the actual logout is intercepted by the firewall's `logout` key, so that controller method body is intentionally unreachable. There is no registration flow by design; admin users are provisioned exclusively via the `app:create-admin` console command, which hashes the password and assigns `ROLE_ADMIN`.

### Entities relations at a glance
`Album` 1—N `Photo` (cascade remove: deleting an album deletes its photos), `Album` N—1 `Photo` for the optional cover (`ON DELETE SET NULL`, so deleting the cover photo just clears the album's cover rather than failing). Both `Album` and `Photo` carry `createdBy`/`updatedBy` ManyToOne links to `User` — controllers that create/update these entities are expected to set these fields from the current security user, there is no automatic Doctrine listener doing it.

### Current implementation state
Only the dashboard stub (`admin/dashboard.html.twig`) and login screen exist on the template/controller side; album/photo CRUD and the public gallery (steps 3-5 below) are not yet built. When implementing them, follow the existing patterns above (attribute routing under `Controller/Admin`, explicit audit-field assignment, migrations generated via `make:migration` rather than hand-written SQL).

## Implementation Roadmap
1. ~~Entities (Album, Photo)~~ — DONE
2. Admin login (form_login, access_control for `/admin`)
3. Admin CRUD (albums, photo upload)
4. Public gallery (album listing, photo viewing)
5. Hero / landing page

## Development Preferences
- Migrations: rollback + fresh migration during dev, not incremental ALTER TABLE chains
- Column order matters: keep it logical (id first, then relations, then data, then audit fields)
- FK column names: explicit `name` in `#[ORM\JoinColumn]`, no auto-generated `_id` suffix on user relations
- Language: communication in Romanian, code in English
