-- AchiveX: blog FAQs, editable article header and custom page schema
-- (same as migration 2026_10_24_000001_blog_faqs_schema_design). Additive only.
alter table public.blog_posts add column if not exists faqs jsonb not null default '[]'::jsonb;
alter table public.blog_posts add column if not exists hero_color text;
alter table public.blog_posts add column if not exists hero_image text;
alter table public.seo_meta add column if not exists schema jsonb;
