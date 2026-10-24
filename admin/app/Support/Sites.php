<?php

namespace App\Support;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Switches the admin between websites. The main database keeps users, sessions and the list of
// sites; everything else (content, media, SEO, logs, settings) follows the selected website.
// Selecting a website points the default database connection at that website's database.
class Sites
{
    private static ?Site $active = null;

    private static ?string $main = null;

    public static function active(): ?Site
    {
        return static::$active;
    }

    // Connection name of the main database while another website is selected, null otherwise.
    public static function mainConnection(): ?string
    {
        return static::$main;
    }

    public static function config(Site $site): array
    {
        $base = config('database.connections.'.(static::$main ?? config('database.default')));

        return array_merge($base, [
            'driver' => 'pgsql',
            'url' => null,
            'host' => $site->db_host,
            'port' => $site->db_port ?: 5432,
            'database' => $site->db_database ?: 'postgres',
            'username' => $site->db_username,
            'password' => $site->db_password,
            'sslmode' => $site->db_sslmode ?: 'require',
            'search_path' => 'admin,public',
            'options' => [\PDO::ATTR_EMULATE_PREPARES => true],
        ]);
    }

    public static function activate(Site $site): void
    {
        static::$main ??= config('database.default');
        config([
            'database.connections.site' => static::config($site),
            'database.default' => 'site',
            'admin.site_url' => $site->site_url,
            'admin.site_public_url' => $site->public_url ?: $site->site_url,
            'cache.prefix' => 'site'.$site->id.'_',
        ]);
        DB::purge('site');
        // Rebuild the cache store so it uses the selected website's database and keys.
        app('cache')->forgetDriver(config('cache.default'));
        static::$active = $site;
    }

    public static function reset(): void
    {
        if (static::$main) {
            config(['database.default' => static::$main]);
            app('cache')->forgetDriver(config('cache.default'));
        }
        static::$active = null;
        static::$main = null;
    }

    // Connects to a website's database and reports what is there. Never changes anything.
    public static function check(Site $site): array
    {
        config(['database.connections.site_check' => static::config($site)]);
        DB::purge('site_check');
        try {
            $db = DB::connection('site_check');
            $db->select('select 1');
            $has = fn ($schema, $table) => (bool) $db->selectOne('select to_regclass(?) as t', ["{$schema}.{$table}"])->t;
            $admin = $has('admin', 'users') && $has('admin', 'migrations');
            $website = $has('public', 'site_settings') && $has('public', 'blog_posts');
            $result = match (true) {
                $admin && $website => ['status' => 'ready', 'message' => 'Connected. The website and admin tables are in place.'],
                $website => ['status' => 'needs_setup', 'message' => 'Connected. The website tables exist, but the admin tables still need to be added (Prepare database).'],
                default => ['status' => 'needs_setup', 'message' => 'Connected, but this database is empty. Use Prepare database to add the tables.'],
            };
        } catch (\Throwable $e) {
            $result = ['status' => 'error', 'message' => 'Could not connect: '.Str::limit(preg_replace('/\s+/', ' ', $e->getMessage()), 220)];
        } finally {
            DB::purge('site_check');
        }

        return $result;
    }

    // Adds the admin and website tables to a website's database. Existing tables are left alone.
    public static function prepare(Site $site): array
    {
        config(['database.connections.site_setup' => static::config($site)]);
        DB::purge('site_setup');
        try {
            DB::connection('site_setup')->statement('create schema if not exists admin');
            Artisan::call('migrate', ['--database' => 'site_setup', '--force' => true]);

            return ['ok' => true, 'output' => trim(Artisan::output())];
        } catch (\Throwable $e) {
            return ['ok' => false, 'output' => Str::limit($e->getMessage(), 400)];
        } finally {
            DB::purge('site_setup');
        }
    }

    // Copies the signed in user's name, email and role into the website's users table, so records
    // that point at a user (activity log, revisions) work there. The password is not copied.
    public static function mirrorUser(User $user): void
    {
        $now = now();
        rescue(fn () => DB::connection('site')->table('users')->updateOrInsert(['id' => $user->id], [
            'name' => $user->name, 'email' => $user->email, 'role' => $user->role,
            'password' => Hash::make(Str::random(48)), 'updated_at' => $now, 'created_at' => $user->created_at ?? $now,
        ]), report: false);
    }
}
