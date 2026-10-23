<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Blog FAQs and editable article header, plus custom JSON-LD schema per page.
// Additive only: new nullable columns on existing website tables.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("alter table public.blog_posts add column if not exists faqs jsonb not null default '[]'::jsonb");
        DB::statement('alter table public.blog_posts add column if not exists hero_color text');
        DB::statement('alter table public.blog_posts add column if not exists hero_image text');
        DB::statement('alter table public.seo_meta add column if not exists schema jsonb');
    }

    public function down(): void
    {
        DB::statement('alter table public.seo_meta drop column if exists schema');
        DB::statement('alter table public.blog_posts drop column if exists hero_image');
        DB::statement('alter table public.blog_posts drop column if exists hero_color');
        DB::statement('alter table public.blog_posts drop column if exists faqs');
    }
};
