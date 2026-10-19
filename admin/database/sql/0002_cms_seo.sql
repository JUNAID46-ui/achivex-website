-- CMS, SEO and analytics tables used by the admin panel and the website.

-- Key/value site settings (contact details, socials, SEO defaults, schema, verification codes)
create table if not exists public.site_settings (
  key text primary key,
  value jsonb not null default '{}'::jsonb,
  updated_at timestamptz not null default now()
);

-- Page builder sections (home page and global blocks)
create table if not exists public.page_sections (
  id bigint generated always as identity primary key,
  page text not null default 'home',
  key text not null,
  label text not null,
  data jsonb not null default '{}'::jsonb,
  sort int not null default 0,
  visible boolean not null default true,
  updated_at timestamptz not null default now(),
  unique (page, key)
);

-- Per URL SEO overrides
create table if not exists public.seo_meta (
  path text primary key,
  title text,
  description text,
  focus_keyword text,
  og_image text,
  canonical text,
  noindex boolean not null default false,
  updated_at timestamptz not null default now()
);

-- Redirect manager
create table if not exists public.redirects (
  id bigint generated always as identity primary key,
  from_path text not null unique,
  to_path text not null,
  status_code int not null default 301 check (status_code in (301, 302, 307, 308)),
  hits int not null default 0,
  active boolean not null default true,
  created_at timestamptz not null default now()
);

-- Analytics: page views and 404s logged by the website
create table if not exists public.page_views (
  id bigint generated always as identity primary key,
  created_at timestamptz not null default now(),
  path text not null check (char_length(path) <= 500),
  referrer text check (referrer is null or char_length(referrer) <= 500),
  device text check (device is null or device in ('desktop', 'mobile', 'tablet')),
  visitor text check (visitor is null or char_length(visitor) <= 64)
);
create index if not exists page_views_created_at_idx on public.page_views (created_at);
create index if not exists page_views_path_idx on public.page_views (path);

create table if not exists public.not_found_log (
  id bigint generated always as identity primary key,
  created_at timestamptz not null default now(),
  path text not null check (char_length(path) <= 500),
  referrer text check (referrer is null or char_length(referrer) <= 500)
);

-- SEO focus fields on blog posts
alter table public.blog_posts add column if not exists focus_keyword text;

alter table public.site_settings enable row level security;
alter table public.page_sections enable row level security;
alter table public.seo_meta enable row level security;
alter table public.redirects enable row level security;
alter table public.page_views enable row level security;
alter table public.not_found_log enable row level security;

create policy "Public can read site settings" on public.site_settings for select to anon, authenticated using (true);
create policy "Public can read visible sections" on public.page_sections for select to anon, authenticated using (visible);
create policy "Public can read seo meta" on public.seo_meta for select to anon, authenticated using (true);
create policy "Public can read active redirects" on public.redirects for select to anon, authenticated using (active);
create policy "Public can log page views" on public.page_views for insert to anon, authenticated with check (true);
create policy "Public can log missing pages" on public.not_found_log for insert to anon, authenticated with check (true);

-- Private schema for the Laravel admin (users, sessions, media, activity). Not exposed through the public API.
create schema if not exists admin;
revoke all on schema admin from public;
