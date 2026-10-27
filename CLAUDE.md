# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Online portfolio for a portrait photographer (Cîrceag Ionuț, brand "Crg Photography"). Public site (home, gallery, contact form) plus an admin-only backend. The photographer only does portrait sessions (individual, couple, corporate) — never present weddings, baptisms or events in site copy.

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

# One-time test setup: .env.test.local (git-ignored) must contain the same DATABASE_URL as .env.local;
# Doctrine appends "_test", so tests run against crg_photography_test, never the dev data.
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Run tests (PHPUnit 11, config in phpunit.dist.xml)
php bin/phpunit
php bin/phpunit tests/Functional/ContactFormTest.php
php bin/phpunit --filter testMethodName

# Icons (Symfony UX Icons): download a new icon into assets/icons before using it
php bin/console ux:icons:import material-symbols:some-icon-name
# Before deploying, make sure every icon used in templates is stored locally
php bin/console ux:icons:lock

# Security advisories for installed packages
composer audit

# Install/refresh importmap-managed JS assets after adding one to importmap.php
php bin/console importmap:install

# List all registered routes (routes are attribute-based, see config/routes.yaml)
php bin/console debug:router
```

## Tech Stack
- **Backend:** PHP 8.2+, Symfony 7.4
- **Database:** MariaDB (XAMPP); dev database `crg_photography`, test database `crg_photography_test`
- **Frontend:** Twig + Bootstrap 5 (via AssetMapper/importmap) + Stimulus (no Turbo — removed on purpose; plain full-page navigation)
- **Icons:** Symfony UX Icons, SVGs stored in `assets/icons/` and rendered inline with `ux_icon()` (no icon fonts, no Google Fonts — avoids sending visitor IPs to third parties)
- **Fonts:** system font stack (`-apple-system`/SF Pro on Apple devices); no web fonts are loaded
- **Rate limiting:** `symfony/rate-limiter` (contact form)
- **Upload:** VichUploaderBundle (installed, not yet configured)
- **Tests:** PHPUnit 11 functional tests + `dama/doctrine-test-bundle` (each test runs in a rolled-back transaction)
- **Asset system:** AssetMapper (not Webpack/Encore)
- **Server:** XAMPP local, `php -S localhost:8000 -t public/` or `symfony serve`

## Database Config
- `DATABASE_URL` and `APP_SECRET` are in `.env.local` (not committed to git)
- Connection shape: `mysql://root@127.0.0.1:3306/crg_photography?serverVersion=...-MariaDB&charset=utf8mb4`
- The test env does not read `.env.local`; it needs `.env.test.local` (also git-ignored) with `DATABASE_URL`

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
- `path` → file path, rendered with `asset()` (assumed relative to `public/` until VichUploader is configured)
- `title`, `description` → optional
- `visible` → boolean (show/hide individual photo)
- `created_by` / `updated_by` → ManyToOne to User

### ContactRequest (`src/Entity/ContactRequest.php`)
Columns: `id`, `name`, `email`, `phone` (nullable), `message`, `created_at`
- Submitted anonymously from `/contact`, so no `created_by`; validation constraints live on the entity

## Column Naming Conventions
- Foreign keys to User: `created_by`, `updated_by` (not `created_by_id`)
- Foreign key for cover: `cover_id`
- Foreign key for album: `album_id`
- Order field: `sort_order` (property: `sortOrder`, avoids SQL reserved word `order`)

## GitHub
- Repository: `https://github.com/ionutvalentin99/CrgPhotography`
- Branch: `main`
- `.env.local`, `.env.*.local` and `.idea/` are in `.gitignore`

## Architecture Notes

### Routing
Routes are defined via `#[Route]` attributes on controllers and auto-imported through `config/routes.yaml`. All controllers are `final`. Admin controllers live under `src/Controller/Admin/` with a class-level route prefix under `/admin` (e.g. `#[Route('/admin/mesaje')]`) and a class-level `#[IsGranted('ROLE_ADMIN')]`; they are additionally covered by the `^/admin` rule in `access_control` (`config/packages/security.yaml`). Keep both — `AdminAccessTest` checks each admin URL redirects anonymous visitors to `/login` and returns 403 to non-admins.

### Auth flow
Custom form-login authentication (`src/Security/Authenticator.php`, a Symfony `AbstractLoginFormAuthenticator`), not the `security:form_login` shortcut. It authenticates by `email` against `App\Entity\User` via `app_user_provider`, supports remember-me, and redirects to `admin_dashboard` on success (or to the originally requested target path). `SecurityController` only renders the login form and defines the `app_logout` route — the actual logout is intercepted by the firewall's `logout` key. There is no registration flow; admin users are provisioned exclusively via `app:create-admin`. The public site deliberately has no link to the admin area.

### CSRF
CSRF is stateless (`config/packages/csrf.yaml`, Symfony's SameOriginCsrfTokenManager): `assets/controllers/csrf_protection_controller.js` generates the token client-side on submit (double-submit cookie). Stimulus must stay installed for this. In functional tests, form submissions pass an `HTTP_ORIGIN` header so the same-origin check succeeds without JavaScript.

### Public site templates
Public pages extend `templates/layout/public.html.twig`, which wraps content in `.landing-page` and includes `partials/_navbar.html.twig` and `partials/_footer.html.twig`; pages only fill `{% block content %}`. Admin pages extend `base.html.twig` directly and use plain Bootstrap. Contact email/phone are defined once as `app.contact_email` / `app.contact_phone` in `config/services.yaml` and exposed to Twig as the `contact_email` / `contact_phone` globals (`config/packages/twig.yaml`).

### Styling
Bootstrap provides structure and components; `assets/styles/app.css` only holds the palette tokens (scoped to `.landing-page`, mapped onto Bootstrap's `--bs-*` variables) and custom components Bootstrap has no utility for. No inline `style=""` attributes — use Bootstrap utilities (e.g. `text-body-secondary` already resolves to the theme's secondary color) or add a class. Note the compiled Bootstrap's `.btn-primary` hardcodes its colors, so themed buttons use the custom `.btn-ink` / `.btn-outline-ink` / `.btn-paper` classes.

### Album listing queries
`AlbumRepository::findVisibleSummaries()` loads visible albums, their cover (fetch join) and visible-photo count (subquery) in one query and returns `App\Model\AlbumSummary` objects; templates must use `summary.photoCount`, not `album.photos|length` (which lazy-loads every photo). `AlbumListingTest::testAlbumListingRunsASingleQuery` guards against N+1 regressions. Album pages load photos via `PhotoRepository::findVisibleByAlbum()`.

### Contact form
`ContactController` handles GET/POST with Symfony Form (`ContactRequestType`), redirects after a successful submit (PRG) and re-renders with 422 on validation errors. Spam protection: an unmapped, visually hidden honeypot field `website` (filled → same success response, nothing stored) and the `contact_form` rate limiter (`config/packages/rate_limiter.yaml`, 5 submissions/hour per client IP → 429). The limiter is keyed by `Request::getClientIp()`, so configure `trusted_proxies` if the site is ever deployed behind a reverse proxy.

### Testing conventions
- Functional tests live in `tests/Functional/`; build fixtures with `tests/Support/EntityFactoryTrait.php`.
- Call `$this->entityManager()->clear()` after creating fixtures and before the request, otherwise the request reuses the in-memory entities (stale inverse collections, wrong query counts).
- The rate limiter's cache pool persists on disk, so contact form tests clear `cache.contact_form_limiter` at the start (see `ContactFormTest::createContactClient()`).

### Entities relations at a glance
`Album` 1—N `Photo` (cascade remove), `Album` N—1 `Photo` for the optional cover (`ON DELETE SET NULL`). Both `Album` and `Photo` carry `createdBy`/`updatedBy` links to `User` — controllers that create/update these entities must set them from the current security user; no Doctrine listener does it.

### Current implementation state
Built: admin login, public home (hero, about, latest 9 albums, CTA), gallery index + album pages, contact form with an admin list of submissions (`/admin/mesaje`). Not built yet: admin CRUD for albums/photos and photo upload (VichUploader is installed but unconfigured), so the album/photo tables are filled manually for now. When implementing it, follow the patterns above (attribute routing under `Controller/Admin`, explicit audit-field assignment, migrations via `make:migration`, functional tests).

## Implementation Roadmap
1. ~~Entities (Album, Photo)~~ — DONE
2. ~~Admin login (custom authenticator, access_control for `/admin`)~~ — DONE
3. Admin CRUD (albums, photo upload)
4. ~~Public gallery (album listing, photo viewing)~~ — DONE
5. ~~Hero / landing page~~ — DONE

## Development Preferences
- Migrations: rollback + fresh migration during dev, not incremental ALTER TABLE chains
- Column order matters: keep it logical (id first, then relations, then data, then audit fields)
- FK column names: explicit `name` in `#[ORM\JoinColumn]`, no auto-generated `_id` suffix on user relations
- Use Bootstrap classes for structure; keep `app.css` for custom things only — don't reimplement what Bootstrap already provides
- Follow Symfony best practices strictly; dead code (including leftovers in recipe-installed files) gets removed
- Language: communication in Romanian, code in English
