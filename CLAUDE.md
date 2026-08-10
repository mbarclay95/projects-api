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

## Companion repo: `projects-api-public`

This repo is IP-restricted at the nginx proxy. A small number of endpoints need to
be reachable from the internet, and those live in a separate repo,
`voyager-org/projects-api-public` (checked out as `backend-public` alongside this
one). It is a thin public façade: no migrations, no schema, hand-written mirrors of
models from here, every route gated by a token.

The two repos are coupled in two directions, and **neither coupling is enforced by
anything** — no compiler, no test, no foreign key. Both are easy to break from this
side without noticing.

### This repo owns every table the public repo reads

Renaming, dropping, or retyping a column on a shared table is a cross-repo breaking
change. The failure mode is a public endpoint returning a 500 to a stranger who was
sent a link, with nobody monitoring it.

Shared tables, to be kept current as the list grows:

| Table | Owned here | Read by `projects-api-public` |
| --- | --- | --- |
| `events` | `App\Models\Events\Event` | `App\Models\Event` |
| `event_participants` | `App\Models\Events\EventParticipant` | `App\Models\EventParticipant` |

When changing a migration that touches one of these, check the public repo in the
same change.

### Keep the Laravel major version in lockstep

The public repo's skeleton and model patterns are lifted from this one by copy. When
one repo's Laravel major version changes, the other changes in the same pair of PRs.

This rule exists because it already went wrong: the public repo sat on Laravel 9
while this one moved to 12, a three-major gap that nobody noticed until a new
feature needed both. Nothing warned about it, because neither repo imports anything
from the other.

Note that this repo runs Laravel 12 on the **legacy pre-11 skeleton** —
`bootstrap/app.php` binds `App\Http\Kernel` and `App\Console\Kernel` as singletons.
That is deliberate and shared with the public repo. Migrating either repo to the
slim skeleton is a decision for both.

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
