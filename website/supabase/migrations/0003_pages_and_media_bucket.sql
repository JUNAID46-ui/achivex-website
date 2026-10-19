-- Editable page content (fixed pages) and pages created in the admin, plus the public image bucket.
create table if not exists public.pages (
  path text primary key,
  kind text not null default 'custom' check (kind in ('builtin', 'custom')),
  title text not null,
  published boolean not null default true,
  content jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
alter table public.pages enable row level security;
create policy "Public can read published pages" on public.pages for select to anon, authenticated using (published);
grant select on public.pages to anon, authenticated;

alter table public.page_sections add column if not exists type text;
update public.page_sections set type = key where type is null;

insert into storage.buckets (id, name, public, file_size_limit)
values ('media', 'media', true, 20971520)
on conflict (id) do nothing;
