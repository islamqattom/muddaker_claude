<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Hard safety guard: refuse to run tests unless the active database is the
     * dedicated test database. This runs immediately after the application
     * boots and BEFORE any test trait (e.g. RefreshDatabase) can migrate or
     * otherwise touch the database, so a misconfigured/cached config can never
     * wipe a non-test database.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $connection = (string) config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($database !== 'quran_system_test') {
            throw new RuntimeException(sprintf(
                'Refusing to run tests: active database is "%s" (connection "%s"), '
                .'expected "quran_system_test". Run `php artisan config:clear` before running tests.',
                (string) $database,
                $connection
            ));
        }
    }
}
