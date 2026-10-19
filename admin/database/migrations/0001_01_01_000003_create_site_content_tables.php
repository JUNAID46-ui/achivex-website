<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Creates the website content tables in the public schema when they do not exist yet
// (they already exist on the AchiveX Supabase project, so this is skipped there).
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            DO $$ BEGIN
              IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'anon') THEN CREATE ROLE anon NOLOGIN; END IF;
              IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'authenticated') THEN CREATE ROLE authenticated NOLOGIN; END IF;
            END $$;
        SQL);

        if (! $this->publicTable('blog_posts')) {
            DB::unprepared(file_get_contents(database_path('sql/0001_site_schema.sql')));
            DB::unprepared(file_get_contents(database_path('sql/seed_content.sql')));
        }
        if (! $this->publicTable('site_settings')) {
            DB::unprepared(file_get_contents(database_path('sql/0002_cms_seo.sql')));
            DB::unprepared(file_get_contents(database_path('sql/seed_cms.sql')));
        }
    }

    public function down(): void
    {
        // Website content is never dropped automatically.
    }

    private function publicTable(string $name): bool
    {
        return (bool) DB::scalar("select to_regclass('public.{$name}') is not null");
    }
};
