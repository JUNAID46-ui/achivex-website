-- AchiveX website schema. Applied to project pktnqerzatrdndhbtsqp.
create table public.hero_stats (id bigint generated always as identity primary key, value text not null, label text not null, sort int not null default 0);
create table public.blog_posts (slug text primary key, title text not null, category text not null, published_on date not null default current_date, author text not null default 'AchiveX Editorial Team', featured boolean not null default false, image text, excerpt text not null, intro_keyword text not null, intro_text text not null, sections jsonb not null default '[]'::jsonb, published boolean not null default true);
create table public.case_studies (slug text primary key, title text not null, client_type text not null, industry text not null, services text[] not null default '{}', image text, summary text not null, overview text not null, challenge jsonb not null default '[]'::jsonb, solution jsonb not null default '[]'::jsonb, results jsonb not null default '[]'::jsonb, sort int not null default 0, published boolean not null default true);
create table public.news_posts (slug text primary key, published_on date not null default current_date, title text not null, summary text not null, image text, body jsonb not null default '[]'::jsonb, published boolean not null default true);
create table public.whitepapers (slug text primary key, title text not null, type text not null default 'Whitepaper', description text not null, cover text, inside jsonb not null default '[]'::jsonb, file_url text, sort int not null default 0, published boolean not null default true);
create table public.job_openings (id bigint generated always as identity primary key, title text not null, team text, location text, employment_type text, description text, sort int not null default 0, published boolean not null default true);
create table public.team_members (id bigint generated always as identity primary key, name text not null, role text not null, photo_url text, linkedin_url text, sort int not null default 0, published boolean not null default true);
create table public.milestones (id bigint generated always as identity primary key, year text not null, title text not null, description text, sort int not null default 0);
create table public.locations (id bigint generated always as identity primary key, city text not null, country text not null, type text not null default 'Office', address text, phone text, email text, is_hq boolean not null default false, sort int not null default 0);
create table public.contact_submissions (id bigint generated always as identity primary key, created_at timestamptz not null default now(), name text not null check (char_length(name) between 1 and 200), email text not null check (char_length(email) between 3 and 320), phone text check (phone is null or char_length(phone) <= 50), service text check (service is null or char_length(service) <= 200), message text not null check (char_length(message) between 1 and 5000));
create table public.whitepaper_requests (id bigint generated always as identity primary key, created_at timestamptz not null default now(), whitepaper_slug text not null references public.whitepapers(slug) on delete cascade, name text not null check (char_length(name) between 1 and 200), email text not null check (char_length(email) between 3 and 320), company text check (company is null or char_length(company) <= 200), job_title text check (job_title is null or char_length(job_title) <= 200));

alter table public.hero_stats enable row level security;
alter table public.blog_posts enable row level security;
alter table public.case_studies enable row level security;
alter table public.news_posts enable row level security;
alter table public.whitepapers enable row level security;
alter table public.job_openings enable row level security;
alter table public.team_members enable row level security;
alter table public.milestones enable row level security;
alter table public.locations enable row level security;
alter table public.contact_submissions enable row level security;
alter table public.whitepaper_requests enable row level security;

create policy "Public can read hero stats" on public.hero_stats for select to anon, authenticated using (true);
create policy "Public can read published blog posts" on public.blog_posts for select to anon, authenticated using (published);
create policy "Public can read published case studies" on public.case_studies for select to anon, authenticated using (published);
create policy "Public can read published news" on public.news_posts for select to anon, authenticated using (published);
create policy "Public can read published whitepapers" on public.whitepapers for select to anon, authenticated using (published);
create policy "Public can read published jobs" on public.job_openings for select to anon, authenticated using (published);
create policy "Public can read published team" on public.team_members for select to anon, authenticated using (published);
create policy "Public can read milestones" on public.milestones for select to anon, authenticated using (true);
create policy "Public can read locations" on public.locations for select to anon, authenticated using (true);
create policy "Public can send contact messages" on public.contact_submissions for insert to anon, authenticated with check (true);
create policy "Public can request whitepapers" on public.whitepaper_requests for insert to anon, authenticated with check (true);
