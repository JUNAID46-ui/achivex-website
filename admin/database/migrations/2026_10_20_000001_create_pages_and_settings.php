<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Pages: content overrides for the fixed website pages and fully custom pages made in the admin.
// Every statement checks first, so it is safe to run on a database that already has these objects.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            create table if not exists public.pages (
              path text primary key,
              kind text not null default 'custom' check (kind in ('builtin', 'custom')),
              title text not null,
              published boolean not null default true,
              content jsonb not null default '{}'::jsonb,
              created_at timestamptz not null default now(),
              updated_at timestamptz not null default now()
            )
        SQL);
        DB::statement('alter table public.pages enable row level security');
        DB::statement(<<<'SQL'
            do $$ begin
              if not exists (select 1 from pg_policies where schemaname = 'public' and tablename = 'pages' and policyname = 'Public can read published pages') then
                create policy "Public can read published pages" on public.pages for select to anon, authenticated using (published);
              end if;
            end $$
        SQL);
        DB::statement('grant select on public.pages to anon, authenticated');

        // Sections can now belong to any page, and several sections of one type can sit on a page.
        DB::statement('alter table public.page_sections add column if not exists type text');
        DB::statement('update public.page_sections set type = key where type is null');

        // Private admin settings (email server, storage key). Lives in the admin schema, never exposed.
        if (! Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->jsonb('value')->default('{}');
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (! Schema::hasColumn('media', 'public_url')) {
            Schema::table('media', fn (Blueprint $table) => $table->string('public_url', 1000)->nullable());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
