# CrgPhotography - AI Context

## Project Overview
Online photography gallery for a photographer. Public-facing gallery with admin-only backend for managing albums and photos.

## Tech Stack
- **Backend:** PHP 8.2+, Symfony 7.4
- **Database:** MariaDB 10.4.32 (XAMPP), database name: `CrgPhotography`
- **Frontend:** Twig + Bootstrap 5 (via AssetMapper/importmap) + Stimulus + Turbo
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
