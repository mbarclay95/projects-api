# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install dependencies
composer install
npm install

# Run tests
./vendor/bin/phpunit
./vendor/bin/phpunit tests/Feature/SomeTest.php   # single test file
./vendor/bin/phpunit --filter testMethodName      # single test method

# Database
php artisan migrate
php artisan migrate:rollback

# Start local database (PostgreSQL via Docker)
docker-compose up

# Build assets
npm run dev
npm run watch-poll   # use this in WSL

# Docker image build and push
./docker/build.sh
```

## Architecture

This is a **Laravel 12 REST API** using JWT authentication, Spatie permissions, and a custom CRUD package (`mbarclay36/laravel-crud`).

### Layered Pattern

All domains follow: **Controller → Repository → Eloquent Model**. Some domains add a **Service layer** between controller and repository for complex business logic (e.g., `RunBackupService`, `GamingBroadcastService`, `ActiveSessionService`).

### Custom CRUD Package

`mbarclay36/laravel-crud` provides:
- `CrudController` base class — controllers extending this get standard REST endpoints (index, store, update, destroy) for free
- `ApiModel` trait — controls which model attributes are exposed in API responses
- Permission-based authorization via `config/crudpackage.php` (`use_model_permission_auth: true`)

Most controllers extend `CrudController` and override only what's domain-specific.

### Domain Organization

Code under `app/` is organized by domain. Each domain typically has models, a controller, one or more repositories, and optionally form request validators and a service:

| Domain | Key files |
|--------|-----------|
| Tasks | `Task`, `RecurringTask`, `Family`, `Tag`, `TaskUserConfig` — 7 repositories |
| Backups | `Backup`, `BackupStep`, `ScheduledBackup`, `Target` + `RunBackupService` |
| Gaming | `GamingSession`, `GamingDevice` + MQTT via `MqttService`, WebSocket via `GamingBroadcastService` |
| Dashboard | `Folder`, `Site`, `Image` — S3 image storage |
| Goals | `Goal`, `GoalDay` |
| Events | `Event`, `EventParticipant` |
| Logging | `LogEvent`, `LogItem` |

### Authentication & Authorization

- JWT bearer tokens (guard: `api`, driver: `jwt` via `php-open-source-saver/jwt-auth`)
- All routes protected by `auth` middleware except `/health-check`, `/login`, and specific public gaming/webhook endpoints
- Role/permission management via Spatie Laravel Permission

### Query Filtering

Complex list endpoints use `EloquentFilter` — filter classes live in `app/ModelFilters/` and are bound to their models. When adding filterable fields, update the corresponding filter class.

### Real-Time Features

- **Laravel Reverb** (WebSockets) for broadcasting events to clients
- **MQTT** (`php-mqtt/client`) for communicating with gaming hardware devices

### File Storage

Configured for local or S3 via `FILESYSTEM_DISK` env var. Site/dashboard images are stored in S3 with path-style endpoints.

### Artisan Commands

Custom commands in `app/Console/Commands/`:
- `backup:run` / `backup:step-completed` — backup pipeline automation
- `gaming:device-communication` — MQTT polling loop
- `dashboard:migrate-db-models` / `dashboard:recalculate-sites-sorting` — maintenance utilities

### Testing Environment

Tests live in `tests/Unit/` and `tests/Feature/`. Feature tests use a real database.

`phpunit.xml` sets `APP_ENV=testing`, so config comes from `.env.testing` — gitignored; copy `.env.testing.example` and fill it in. The database named there must already exist, as `RefreshDatabase` migrates and truncates it but will not create it. Check `phpunit.xml` for environment overrides (array cache, sync queue, array session driver).

CI does not use `.env.testing`. The workflow in `.gitea/workflows/` supplies the same values as environment variables, which take precedence over any env file.

That precedence cuts both ways: a real `DB_*` environment variable beats `.env.testing`, because Dotenv is immutable and will not overwrite what is already set. Do not put `DB_*` in the compose file's `environment:` block — doing so silently points the test suite at the dev database, and `RefreshDatabase` drops every table in it.

`tests/CreatesApplication.php` guards against this with an allowlist: the suite refuses to run against any database not named in `ALLOWED_TEST_DATABASES`. Add a new test database there deliberately rather than loosening the check — CI runs on the same server as production.
