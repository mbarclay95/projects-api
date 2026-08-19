<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use RuntimeException;

trait CreatesApplication
{
    /**
     * The only databases the suite may run against.
     *
     * An allowlist rather than a name pattern because CI runs on the same
     * server as production. A pattern would let anything matching it through;
     * this refuses everything that is not named here, so a new environment has
     * to be added deliberately.
     */
    private const ALLOWED_TEST_DATABASES = [
        'project_app_testing',  // local, docker-compose
        'projects-testing',     // CI, .gitea/workflows/test-build-deploy.yml
    ];

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstNonTestDatabase();

        return $app;
    }

    /**
     * Refuse to run against any database not named in ALLOWED_TEST_DATABASES.
     *
     * RefreshDatabase migrates fresh and truncates whatever connection it is
     * handed, so pointing it at the development database destroys it silently.
     * Two things make that easy to do by accident:
     *
     * phpunit.xml sets APP_ENV=testing, so config should come from .env.testing.
     * But real environment variables win over any env file — Dotenv is immutable
     * and will not overwrite what is already set — so a DB_DATABASE exported by
     * docker-compose overrides .env.testing without any warning. And when
     * .env.testing is absent entirely, Laravel quietly falls back to .env.
     *
     * The check lives here rather than in an overridden refreshDatabase()
     * because a class re-declaring 'use RefreshDatabase' would take the trait's
     * method in preference to an inherited override, silently disabling it.
     * createApplication() runs for every test before any database work.
     */
    private function guardAgainstNonTestDatabase(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if (in_array($database, self::ALLOWED_TEST_DATABASES, true)) {
            return;
        }

        throw new RuntimeException(
            "Refusing to run tests against database '{$database}': it is not one of the "
            .'databases this suite is allowed to touch, and RefreshDatabase would drop every '
            .'table in it.'.PHP_EOL.PHP_EOL
            .'Allowed: '.implode(', ', self::ALLOWED_TEST_DATABASES).PHP_EOL.PHP_EOL
            .'Copy .env.testing.example to .env.testing and point it at one of those. If that '
            .'is already done, check for a DB_DATABASE environment variable overriding it — a '
            .'real environment variable beats any env file. Add a genuinely new test database '
            .'to ALLOWED_TEST_DATABASES in '.basename(__FILE__).'.'
        );
    }
}
