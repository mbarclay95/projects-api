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

Note that this repo runs Laravel 13 on the **legacy pre-11 skeleton** —
`bootstrap/app.php` binds `App\Http\Kernel` and `App\Console\Kernel` as singletons.
That is deliberate and shared with the public repo. Migrating either repo to the
slim skeleton is a decision for both.

## Architecture

This is a **Laravel 13 REST API** using JWT authentication, Spatie permissions, and a custom CRUD package (`mbarclay36/laravel-crud`).

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
| Tasks | `Task`, `RecurringTask`, `TaskUserConfig` — 5 repositories |
| UserGroups | `UserGroup`, `UserGroupUser` |
| Tags | `Tag` |
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

### Morph map

Polymorphic `*_type` columns hold short aliases, not PHP class names:
`user`, `user-group`, `task`, `recurring-task`. The map is registered with
`Relation::enforceMorphMap()` in `AppServiceProvider::boot()`, so calling
`getMorphClass()` on a model outside the map throws
`ClassMorphViolationException` instead of writing its class name into a column.
Use `getMorphClass()`, never `Relation::getMorphAlias()` — the latter falls
back to the class name for an unmapped model instead of throwing, which
defeats the enforcement. Adding a new morphable model means adding it to the
map first.

The five columns holding these aliases: `tasks.owner_type`,
`recurring_tasks.owner_type`, `taggables.taggable_type`,
`model_has_roles.model_type`, `model_has_permissions.model_type` (the latter
two are Spatie's permission tables).

### User-group membership

Membership is a row in `user_group_user`, a plain pivot of `user_group_id` and
`user_id` that also carries a denormalised `scope`, copied from the group.
`UserGroup::members()` reads it; `User::taskGroup()` reads it filtered to
`scope = 'tasks'`. `UserGroup::syncMembers()` is the only writer. `attach()`
and `detach()` on the pivot happen for every scope; removing a member always
deletes their `user_group_user` row, and for a tasks-scoped group also closes
their current `task_user_configs` window rather than deleting past rows. A
grocery group has no `task_user_configs` rows to close.

That guard is why "a member of a group" and "someone who does chores" are no
longer the same statement: `syncMembers()` writes a `TaskUserConfig`, on both
the attach and the detach side, only when `$this->scope === FeatureEnum::TASKS->value`.

A user can be in at most one group per scope, enforced by a unique index on
`(user_id, scope)`. A group's scope is set at creation and never updated —
`UserGroupsRepository::updateEntity()` does not touch it — which is what keeps
the pivot's denormalised copy from drifting: there is no scope change for the
two to disagree about.

`scope` is `not null` with no column default, so every writer has to name it
explicitly rather than fall back to one. Four places do:
`UserGroupsRepository::createEntity()`, `UserGroupFactory::definition()`,
`UserGroup::syncMembers()` (which copies `$this->scope` onto each pivot row),
and the tests that call `members()->attach()` directly rather than going
through `syncMembers()`. Miss one and the failure is a loud not-null
violation, not a silent mis-scope.

`task_user_configs` holds dated per-week chore settings
(`tasks_per_week`, `default_tasks_per_week`, `start_date`, `end_date`) and is
not a membership table for any scope. Because removal preserves history
instead of deleting rows, the distinct `(user_group_id, user_id)` pairs in it
are no longer the member list and must not be used as one — and now that a
non-tasks scope exists, it is not even every member's list, only a
tasks-scoped group's.

### UserGroups and tags live outside Tasks

`App\Models\UserGroups\UserGroup`, `App\Models\UserGroups\UserGroupUser` and
`App\Models\Tags\Tag` are shared models, deliberately kept outside any one
feature's namespace rather than owned by Tasks. The task-specific settings on
`UserGroup` live in a `config` jsonb column keyed by snake_case
(`task_strategy`, `task_points`) — that is a storage decision, not the
`/user-groups` API shape: `taskStrategy` and `taskPoints` stay top-level
fields on the payload, produced by `getTaskStrategyAttribute()` and
`getTaskPointsAttribute()` reading out of `config`. `userConfigs()` and the
`TaskUserConfig` writes in `syncMembers()` are still tasks-only, now gated on
`scope` rather than being the only path there is.

`scope` stays a plain, uncast string column — **do not add
`'scope' => FeatureEnum::class` to `$casts`.** It looks like the natural move
once the enum exists, and it breaks `syncMembers()`, which copies
`$this->scope` straight into `attach($id, ['scope' => $this->scope])`; the
pivot wants a string, not an enum instance.

**Per-scope validation lives in the repository, not the controller.**
`CrudController` validates `static::$storeRules` / `$updateRules` before it
has looked at the model, so an update request cannot see the group's scope —
taking it from the request body would let a caller declare a tasks group to be
grocery and skip `taskStrategy`'s `required`. `UserGroupsRepository` resolves
the scope instead — from `$request['scope']` on create, from `$model->scope`
on update — and runs a second validation stage,
`Validator::make($request, $scope->groupConfigRules())->validate()`.
`ValidationException` renders as a 422 from anywhere in the request, so the
repository's rejection is a 422 exactly like the controller's.

The line drawn here is the group concept, not the vocabulary that uses it:
`UserGroup` and its table are renamed end to end, but "family" stays the
Tasks feature's own word for a tasks-scoped group, and a per-scope label would
say the same thing for any other scope. `FamilyStatsController`,
`FamilyMemberStatsRepository`, `FamilyMemberStatsApiModel` and
`FamilyTaskStrategyEnum` are deliberate survivors of that line, not leftovers
from an incomplete rename.

**Permission names are the model's fully-qualified class name** —
`HasCrudPermissions` builds `permissions.name` as `static::class . '_view_any'`
and friends — so moving a model's namespace orphans its old `permissions` rows.
This no longer needs a migration: `RolesAndPermissionsSeeder` deletes any
permission it doesn't declare on the run just finished, and `docker/start.sh`
runs it on every container boot, so an old class name's permissions are pruned
automatically and its role grants re-created under the new name. The pruned
rows get fresh ids — nothing depends on a permission's id staying stable
across a rename.

The seeder still needs `PermissionRegistrar::forgetCachedPermissions()` around
the prune (it already calls it at both ends of `run()`) — Spatie caches the
permission list for 24 hours behind the file cache driver, and without the
flush a correct prune looks like it did nothing.

`Tests\TestCase` seeds `RolesAndPermissionsSeeder` against an empty schema, so
the prune step never has anything to delete in a test run — a seeder that
declared the wrong permission name would still leave the suite green. Checking
the `permissions` table on the dev database by hand is what actually verifies
a rename happened.

`UsersRepository`'s second argument to `toApiModels()` is a *hide* list, not a
show list — it names attributes to exclude from the `/users` payload by
string. Renaming an attribute that appears in it stops the entry from
matching, which silently un-hides the attribute instead of failing to build:
the current entry is `group_ids`, renamed from `task_group_id` when `/me`'s
map grew a key per group scope. Nothing asserts the `/users` payload shape, so
a stale hide-list entry ships green.

### Tag scopes and group scopes

`GET /tags` requires a `scope`; a request without one is a 422, not a default.
Nothing on a `tags` row says which feature it belongs to, and there is not
going to be one — the pool of tags a feature offers is defined the same way
its rows are defined: a relation on `Tag` plus that feature's own visibility
rule (a task is owned by a user *or* a family, so the tasks scope is an
`orWhere` over both; a differently-owned feature gets a different rule, not
the same one reused).

The tasks scope covers `tasks` alone, and that is complete rather than a gap.
`Task::updateTags()` is the only tag writer in the app. `RecurringTask::tags()`
is declared but nothing ever writes through it — a recurring task hands its
tag strings to the `Task` it generates, which ends in that same
`updateTags()`. Every row in `taggables` is therefore a `task` row; there is
nothing a `recurring-task` clause would catch.

Everything that varies by scope — for tags or for user groups — lives on
`App\Enums\FeatureEnum`: a case per feature, plus two arrays over its cases.

| | |
|---|---|
| `TAG_SCOPES` | features with a tag list — an arm of the `match` in `TagsRepository::getEntities()` |
| `GROUP_SCOPES` | features that can have a `user_groups` row — an arm of `groupConfigRules()` and `buildConfig()` |

A case in neither array simply does not exist to either mechanism: `grocery`
is in `GROUP_SCOPES` and not in `TAG_SCOPES`, so a grocery group can be
created while `GET /tags?scope=grocery` stays a 422. Adding a feature is a
case, membership in whichever arrays it opts into, and a `match` arm wherever
it opted in — the arms are what fail loudly. `TagsRepository::getEntities()`'s
`match` and `FeatureEnum::groupConfigRules()` / `buildConfig()` throw
`UnhandledMatchError` on a case with no arm, rather than silently returning
the wrong thing. The arrays are deliberately the silent half.

**The `in:` lists are built from these arrays, not hand-extended.**
`Rule::in()` cannot run in a static property initializer, so
`TagController::$indexRules` and `UserGroupController::$storeRules` are
assigned in each controller's **constructor**, where a function call is legal
— `Rule::in(FeatureEnum::tagScopeValues())` and
`Rule::in(FeatureEnum::groupScopeValues())` respectively. Both controllers
still **declare** the static property being assigned
(`protected static array $indexRules = [];` / `$storeRules = [];`) — a
subclass that drops the redeclaration writes to `CrudController`'s own slot,
which every other controller shares, and `CrudController` reads all three
properties through `static::` inside `index()` / `store()` / `update()`,
after construction, so a constructor assignment is in time.

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

### There is no migration history

`database/migrations/` is empty. The history was squashed into `database/schema/pgsql-schema.sql`, and new migrations accumulate on top of it as normal — `migrate` loads the schema file only when the target database has no migration history, then runs anything newer.

Because that load shells out to `psql`, **a Postgres client is required wherever a database is built from scratch** — the dev image and CI both install `postgresql-client` for this reason. The production image deliberately does not: its database is already migrated, so the schema file is never loaded there. A fresh production database would need the client added.

If the schema is ever re-dumped, `pg_dump` 17 re-emits the header line `SET transaction_timeout = 0;`, which the PostgreSQL 16 server here rejects. It has to be deleted from the dump again, in both places it appears.

Squashing again later is the same three steps: `migrate:fresh --env=testing`, `schema:dump --prune --env=testing`, then strip that header.
