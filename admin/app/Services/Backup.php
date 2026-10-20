<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Website backups as one JSON file: every content table, and optionally the inbox and analytics.
// Admin accounts, sessions and saved secrets are never included, so a backup file cannot leak them.
// Uploaded media files stay in Supabase Storage; the backup keeps the media library rows that point to them.
class Backup
{
    public const FORMAT = 'achivex-backup';
    public const VERSION = 1;

    // Restore order: tables that others refer to come first.
    public const GROUPS = [
        'content' => [
            'public.site_settings', 'public.seo_meta', 'public.redirects', 'public.pages', 'public.page_sections',
            'public.hero_stats', 'public.blog_posts', 'public.case_studies', 'public.news_posts', 'public.whitepapers',
            'public.job_openings', 'public.team_members', 'public.milestones', 'public.locations', 'admin.media',
        ],
        'inbox' => ['public.contact_submissions', 'public.whitepaper_requests'],
        'analytics' => ['public.page_views', 'public.not_found_log'],
    ];

    public static function tables(array $groups): array
    {
        return collect(self::GROUPS)->only(array_merge(['content'], $groups))->flatten()->all();
    }

    // Streams the backup so large analytics tables never sit in memory all at once.
    public static function write($out, array $tables): void
    {
        fwrite($out, '{"format":'.json_encode(self::FORMAT).',"version":'.self::VERSION.',"created_at":'.json_encode(now()->toIso8601String()).',"tables":{');
        foreach (array_values($tables) as $i => $table) {
            fwrite($out, ($i ? ',' : '').json_encode($table).':[');
            $first = true;
            $key = self::primaryKey($table);
            DB::table($table)->orderBy($key)->lazyById(500, $key)->each(function ($row) use ($out, &$first) {
                fwrite($out, ($first ? '' : ',').json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $first = false;
            });
            fwrite($out, ']');
        }
        fwrite($out, '}}');
    }

    // Checks a decoded backup file and returns [table => rows] for the tables it holds.
    public static function parse(?array $data): array
    {
        $fail = fn (string $message) => throw ValidationException::withMessages(['file' => $message]);
        if (($data['format'] ?? null) !== self::FORMAT || ! is_array($data['tables'] ?? null)) {
            $fail('This is not an AchiveX backup file.');
        }
        if ((int) ($data['version'] ?? 0) > self::VERSION) {
            $fail('This backup was made by a newer version of the admin.');
        }
        $allowed = collect(self::GROUPS)->flatten()->all();
        foreach ($data['tables'] as $table => $rows) {
            if (! in_array($table, $allowed, true)) {
                $fail("The backup contains a table that cannot be restored: {$table}.");
            }
            if (! is_array($rows) || collect($rows)->contains(fn ($r) => ! is_array($r))) {
                $fail("The rows for {$table} are damaged.");
            }
        }

        // Keep the restore order fixed, whatever order the file uses.
        return collect($allowed)->filter(fn ($t) => array_key_exists($t, $data['tables']))
            ->mapWithKeys(fn ($t) => [$t => $data['tables'][$t]])->all();
    }

    // Merge adds and updates rows. Replace also removes rows that are not in the backup.
    // Everything runs in one transaction, so a failed restore changes nothing.
    public static function restore(array $tables, bool $replace): array
    {
        $counts = [];
        DB::transaction(function () use ($tables, $replace, &$counts) {
            if ($replace) {
                foreach (array_reverse(array_keys($tables)) as $table) {
                    DB::table($table)->delete();
                }
            }
            foreach ($tables as $table => $rows) {
                $columns = self::columns($table);
                $key = self::primaryKey($table);
                $identity = self::hasIdentity($table);
                foreach (array_chunk($rows, 200) as $chunk) {
                    self::upsert($table, $columns, $key, $identity, $chunk);
                }
                if (self::sequence($table, $key)) {
                    DB::statement("select setval(pg_get_serial_sequence(?, ?), greatest((select coalesce(max({$key}), 0) from {$table}), 1))", [$table, $key]);
                }
                $counts[$table] = count($rows);
            }
        });

        return $counts;
    }

    private static function upsert(string $table, array $columns, string $key, bool $identity, array $rows): void
    {
        // Only real columns of the table are written, so a crafted file cannot inject SQL through names.
        $cols = array_values(array_intersect($columns, array_keys($rows[0] ?? [])));
        if (! $cols || ! in_array($key, $cols, true)) {
            throw ValidationException::withMessages(['file' => "The rows for {$table} have no {$key} column."]);
        }
        $bindings = [];
        $values = [];
        foreach ($rows as $row) {
            $values[] = '('.implode(',', array_fill(0, count($cols), '?')).')';
            foreach ($cols as $c) {
                $v = $row[$c] ?? null;
                $bindings[] = match (true) {
                    is_bool($v) => $v ? 'true' : 'false',
                    is_array($v) => json_encode($v),
                    default => $v,
                };
            }
        }
        $quoted = implode(',', array_map(fn ($c) => '"'.$c.'"', $cols));
        $updates = collect($cols)->reject(fn ($c) => $c === $key)->map(fn ($c) => "\"{$c}\" = excluded.\"{$c}\"")->implode(',');
        $sql = "insert into {$table} ({$quoted})".($identity ? ' overriding system value' : '').' values '.implode(',', $values)
            ." on conflict (\"{$key}\") do ".($updates ? "update set {$updates}" : 'nothing');
        DB::statement($sql, $bindings);
    }

    private static function columns(string $table): array
    {
        [$schema, $name] = explode('.', $table);

        return DB::table('information_schema.columns')->where('table_schema', $schema)->where('table_name', $name)->pluck('column_name')->all();
    }

    private static function primaryKey(string $table): string
    {
        return DB::selectOne('select a.attname from pg_index i join pg_attribute a on a.attrelid = i.indrelid and a.attnum = any(i.indkey) where i.indrelid = ?::regclass and i.indisprimary', [$table])->attname;
    }

    private static function hasIdentity(string $table): bool
    {
        [$schema, $name] = explode('.', $table);

        return DB::table('information_schema.columns')->where('table_schema', $schema)->where('table_name', $name)->where('is_identity', 'YES')->exists();
    }

    private static function sequence(string $table, string $key): bool
    {
        return (bool) DB::selectOne('select pg_get_serial_sequence(?, ?) as s', [$table, $key])?->s;
    }
}
