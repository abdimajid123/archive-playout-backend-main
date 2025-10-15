## Archive Playout Backend — Neon (PostgreSQL) Setup and Recovery

### What changed (code edits)

-   database/migrations/0001_01_01_000000_create_users_table.php
    -   Added `public $withinTransaction = false;`
    -   Kept `email` as `string(255)->unique()` for PostgreSQL.
-   database/migrations/0001_01_01_000001_create_cache_table.php
    -   Added `public $withinTransaction = false;`
-   database/migrations/0001_01_01_000002_create_jobs_table.php
    -   Added `public $withinTransaction = false;`
-   database/migrations/2025_02_17_140153_create_personal_access_tokens_table.php
    -   Added `public $withinTransaction = false;`
-   database/migrations/2025_03_10_090504_create_contents_table.php
    -   Added `public $withinTransaction = false;`
    -   Replaced MySQL-only `$table->year('year')` with `$table->integer('year')->nullable()`.
-   database/migrations/2025_04_21_103737_create_rawfiles_table.php
    -   (No schema change needed.)
-   database/migrations/2025_05_13_102634_create_schedule_slots_table.php
    -   Added `public $withinTransaction = false;`
-   database/migrations/2025_05_13_103051_create_content_schedules_table.php
    -   Added `public $withinTransaction = false;`
    -   Replaced manual FKs with Postgres-safe helpers:
        -   `$table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();`
        -   `$table->foreignId('slot_id')->constrained('schedule_slots')->cascadeOnDelete();`
-   database/migrations/2025_05_17_110208_create_channel_rules_table.php
    -   Added `public $withinTransaction = false;`
-   database/migrations/2025_06_11_143554_create_content_cooldowns_table.php
    -   Added `public $withinTransaction = false;`

Reason: Disabling migration transactions on Postgres helped surface original errors and avoid hidden failures during index/constraint creation. FK helpers ensure correct order and constraints.

### Required .env (Neon)

Set these in `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=<your_neon_host>
DB_PORT=5432
DB_DATABASE=<your_db>
DB_USERNAME=<your_user>
DB_PASSWORD=<your_password>
```

Ensure PHP has Postgres extensions enabled:

```
php -m | findstr /I "pgsql"
# expect: pdo_pgsql, pgsql
```

### Normal reset flow (use when schema/data needs a clean slate)

```
php artisan config:clear
php artisan cache:clear
php artisan db:wipe --force
php artisan migrate --force
php artisan db:seed --force
```

Notes:

-   Wipe drops all tables on the configured database. Double‑check `.env` before running.
-   Seeding creates default users and any required starter data from `DatabaseSeeder`.

### Fast verification

```
php artisan migrate:status
php artisan tinker --execute="dump(DB::table('users')->count());"
```

### Common errors and fixes

-   SQLSTATE[25P02] In failed SQL transaction
    -   Cause: a previous statement failed inside a transaction. Retry after fixing the first error, or run migrations again after code changes.
-   SQLSTATE[42P01] relation "..." does not exist
    -   Cause: foreign key references a table not yet created. Use `foreignId()->constrained()` and verify migration order.
-   MySQL-specific types (e.g., `year()`)
    -   Replace with Postgres-friendly types (e.g., `integer`).
-   Access denied / wrong driver
    -   Ensure `.env` points to Neon (`DB_CONNECTION=pgsql`) and PHP has `pdo_pgsql`/`pgsql` enabled.

### Creating users with specific roles

-   Via seeder (`database/seeders/DatabaseSeeder.php`).
-   Via API (requires admin to change roles).
-   Via Tinker:

```
php artisan tinker --execute="\\App\\Models\\User::create(['name'=>'Archive User','email'=>'archive@example.com','password'=>bcrypt('password'),'role'=>'archive'])"
```

### Rollback / Re-run

If a migration change is made:

```
php artisan migrate:fresh --force
php artisan db:seed --force
```

This drops and recreates all tables, then reseeds.
