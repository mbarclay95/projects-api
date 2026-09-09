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
| Grocery | `GroceryItem`, `GroceryListItem` — `GroceryItemController`, `GroceryListItemController`, `GroceryItemsRepository`, `GroceryListItemsRepository` |
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
`user`, `user-group`, `task`, `recurring-task`, `grocery-item`. The map is
registered with `Relation::enforceMorphMap()` in `AppServiceProvider::boot()`,
so calling `getMorphClass()` on a model outside the map throws
`ClassMorphViolationException` instead of writing its class name into a column.
Use `getMorphClass()`, never `Relation::getMorphAlias()` — the latter falls
back to the class name for an unmapped model instead of throwing, which
defeats the enforcement. Adding a new morphable model means adding it to the
map first.

The five columns holding these aliases: `tasks.owner_type`,
`recurring_tasks.owner_type`, `taggables.taggable_type`,
`model_has_roles.model_type`, `model_has_permissions.model_type` (the latter
two are Spatie's permission tables). `grocery_items` has no morph column of
its own — it doesn't need one, and it only widens `taggables.taggable_type`,
which now holds `task` and `grocery-item` rather than `task` alone.

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
rule.

Two scopes exist, each with its own rule. The tasks scope covers a task owned
by a user *or* a family, an `orWhere` over both. The grocery scope is simpler
because ownership is simpler: tags on grocery items whose `user_group_id` is
the caller's grocery group, resolved through `User::groceryGroup()`; a caller
with no grocery group gets an empty collection from an early return, not a
query with a null in it. A differently-owned feature gets its own rule, not
one of these reused.

`App\Traits\HasTags` — `tags()` and `updateTags()` — is the app's single tag
writer, used by both `Task` and `GroceryItem`. `RecurringTask::tags()` is
declared but nothing ever writes through it — a recurring task hands its tag
strings to the `Task` it generates, which ends in that same `updateTags()`.
`taggables` therefore holds only `task` and `grocery-item` rows.

The two features' tag pools stay separate by design: a `costco` tag entered on
a task and a `costco` tag entered on a grocery item are two `tags` rows,
permanently, and neither scope's list ever surfaces the other's.

Everything that varies by scope — for tags or for user groups — lives on
`App\Enums\FeatureEnum`: a case per feature, plus two arrays over its cases.

| | |
|---|---|
| `TAG_SCOPES` | features with a tag list — an arm of the `match` in `TagsRepository::getEntities()` |
| `GROUP_SCOPES` | features that can have a `user_groups` row — an arm of `groupConfigRules()` and `buildConfig()` |

`grocery` is in both arrays now. A case in neither simply does not exist to
either mechanism. Adding a feature is a case, membership in whichever arrays it
opts into, and a `match` arm wherever it opted in — the arms are what fail
loudly. `TagsRepository::getEntities()`'s `match` and
`FeatureEnum::groupConfigRules()` / `buildConfig()` throw `UnhandledMatchError`
on a case with no arm, rather than silently returning the wrong thing. The
arrays are deliberately the silent half.

### Grocery ownership

A `grocery_items` row belongs to a grocery-scoped `user_groups` row through a
plain `user_group_id` integer — no `owner_type`, because nothing in the
feature wants a personal grocery item the way tasks want a personal task.
`User::groceryGroup()` is a `HasOneThrough` mirroring `taskGroup()`, filtered
to `user_group_user.scope = 'grocery'`.

`GroceryItemController` and `GroceryListItemController` both override
`cannotUpdate()` and `cannotDestroy()` to add
`$model->user_group_id !== $user->groceryGroup?->id` alongside the permission
check — the package's own `_for_user` fallback compares `$model->user_id`, a
column neither table has. This is the same hazard already written up for
Drafts (`createDraftsRole()`'s comment in `RolesAndPermissionsSeeder`): the
role must be granted `updateForUserPermission()` / `deleteForUserPermission()`
for both models and never the unscoped pair, because either unscoped grant
satisfies `CrudController`'s check before the override's second clause runs.
Both overrides also drop the package's `catch (PermissionDoesNotExist)`, so a
missing permission row is a 500, not a 401.

A name is unique within a group, case-insensitively: `GroceryItemsRepository`
compares `lower(name)` on both create and update, throwing a
`ValidationException` rather than relying on a database constraint, so a
duplicate name is a 422 with a message rather than a 500 from a failed insert.

Every item declares how it's quantified: `unit`, a required `App\Enums\GroceryItemUnit`
(`NONE`, `WEIGHT`, `COUNT`) with no database default, so the frontend always sends
one explicitly. `default_quantity` is a nullable decimal alongside it. `GroceryItemsRepository`
rejects a non-null `default_quantity` on a `NONE` item with a 422; nothing constrains
the other two, since a household may not always know a default when they enter an
item. `WEIGHT` is assumed to be pounds — there's no sub-unit column.

`grocery_list_items` is the shopping list: one row per `grocery_item_id`
currently on it, carrying `user_group_id`, `added_by_user_id`, a nullable
`quantity` and a nullable `bought_at` — all bare indexed integers with no
foreign keys, the same reasoning as `grocery_items.user_group_id`. Its
`user_group_id` duplicates what the entry's `grocery_items` row already says;
that is deliberate and safe for the same reason `user_group_user.scope` is
safe — an item's group is set at creation and there is no request field that
changes it, so the two can never disagree — and it buys a plain indexed
`where` for `GroceryListItemsRepository::getEntities()` and a
`cannotUpdate()` / `cannotDestroy()` pair that read like
`GroceryItemController`'s, rather than a `whereHas` through the item on every
check.

Buying an entry sets `bought_at` rather than deleting the row: `getEntities()`
returns `whereNull('bought_at')` only, so a bought entry drops off the index
the instant it's ticked, and the row stays in the table for good — nothing
reads it yet, but a later "what do we usually buy" view could.
`GroceryListItem::getBoughtAttribute()` exposes this as a `bought` boolean
rather than the timestamp; `updateEntity()` writes `bought_at` as
`$model->bought_at ?? now()` when `bought` is true and `null` when it's
false, so a client never invents its own timestamp. Because bought rows are
kept, uniqueness is enforced over unbought rows only — an item with an
unbought entry can't be added again, but an item whose only entries are
bought can. That's a `ValidationException` in the repository rather than a
partial unique index, the same call `GroceryItemsRepository` makes for its
own name-uniqueness rule and for the same reason: a 422 with a message rather
than a 500 from a failed insert.

`default_quantity` is what the Add Items picker seeds a new entry's
`quantity` from — the repository stores exactly what it's sent and defaults
nothing itself. An entry's quantity follows its item's `unit`:
`GroceryListItemsRepository` nulls it silently, on both create and update,
whenever `$item->unit === GroceryItemUnit::NONE`, rather than rejecting it
with a 422 the way the master list does. The difference is who can produce
the bad state: the master-list modal lets someone type a number for a `NONE`
item and can tell them why it was refused, but nothing in the shopping-list
UI can produce one, and a 422 here would strand an entry whose item was
changed to `NONE` after the entry was created — ticking it off PUTs the whole
entity, stale quantity included, and the entry could never be bought.

Deleting a master-list item takes its shopping-list entries with it, bought
and unbought alike: `GroceryItemsRepository::destroyEntity()` deletes
`$model->listItems()` before deleting the item itself, so nothing is left
pointing at a `grocery_items` row that no longer exists.

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
