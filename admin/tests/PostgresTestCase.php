<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Feature tests on a throwaway local PostgreSQL database built from the production table layout
// (tests/fixtures/schema.sql) plus the pending migrations. Never touches Supabase.
// Set TEST_PG_HOST (and optionally TEST_PG_PORT) to run them; they are skipped otherwise.
abstract class PostgresTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('TEST_PG_HOST')) {
            $this->markTestSkipped('Set TEST_PG_HOST to run the PostgreSQL feature tests.');
        }
        $base = ['driver' => 'pgsql', 'host' => getenv('TEST_PG_HOST'), 'port' => getenv('TEST_PG_PORT') ?: 5432, 'username' => 'postgres', 'password' => '', 'charset' => 'utf8', 'prefix' => '', 'search_path' => 'admin,public', 'sslmode' => 'disable'];
        config(['database.connections.setup' => $base + ['database' => 'postgres']]);
        DB::connection('setup')->statement('drop database if exists achivex_feature_test with (force)');
        DB::connection('setup')->statement('create database achivex_feature_test');
        DB::purge('setup');
        config(['database.connections.pgsql' => $base + ['database' => 'achivex_feature_test'], 'database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('pgsql');
        DB::unprepared(file_get_contents(base_path('tests/fixtures/schema.sql')));
        $done = ['0001_01_01_000000_create_users_table', '0001_01_01_000001_create_cache_table', '0001_01_01_000002_create_jobs_table',
            '0001_01_01_000003_create_site_content_tables', '2026_10_19_000001_create_admin_tables', '2026_10_20_000001_create_pages_and_settings',
            '2026_10_21_000001_add_inbox_notifications', '2026_10_22_000001_add_two_factor_to_users'];
        foreach ($done as $m) {
            DB::table('migrations')->insert(['migration' => $m, 'batch' => 1]);
        }
        Artisan::call('migrate', ['--force' => true]);
        \App\Models\Content\SiteSetting::flush();
    }

    protected function makeUser(string $role = 'administrator', string $email = null): User
    {
        return User::create(['name' => ucfirst($role).' User', 'email' => $email ?? $role.'@example.com', 'password' => 'correct-horse-9', 'role' => $role]);
    }
}
