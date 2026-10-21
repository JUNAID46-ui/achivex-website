-- AchiveX admin phase 1 (same as migration 2026_10_23_000001_admin_phase1).
-- Additive only: new tables, new nullable columns, an append only trigger on the activity log,
-- and scheduled publishing in the website read policies. No data is deleted.
-- Run once in Supabase: SQL Editor, New query, paste, Run.

alter table admin.activity_logs
  add column if not exists user_name varchar(120),
  add column if not exists user_role varchar(30),
  add column if not exists module varchar(60),
  add column if not exists subject_id varchar(191),
  add column if not exists status varchar(20) not null default 'success',
  add column if not exists context jsonb;
create index if not exists activity_logs_action_created_at_index on admin.activity_logs (action, created_at);

create table if not exists admin.notifications (
  id bigserial primary key,
  user_id bigint not null references admin.users(id) on delete cascade,
  type varchar(60) not null,
  severity varchar(20) not null default 'info',
  title varchar(255) not null,
  body text,
  module varchar(60),
  url varchar(500),
  dedupe_key varchar(191),
  read_at timestamp(0) without time zone,
  archived_at timestamp(0) without time zone,
  created_at timestamp(0) without time zone not null default CURRENT_TIMESTAMP
);
create index if not exists notifications_user_id_archived_at_read_at_index on admin.notifications (user_id, archived_at, read_at);
create index if not exists notifications_dedupe_key_created_at_index on admin.notifications (dedupe_key, created_at);

create table if not exists admin.content_revisions (
  id bigserial primary key,
  resource varchar(60) not null,
  record_key varchar(191) not null,
  action varchar(20) not null,
  data jsonb not null,
  user_id bigint references admin.users(id) on delete set null,
  user_name varchar(120),
  created_at timestamp(0) without time zone not null default CURRENT_TIMESTAMP
);
create index if not exists content_revisions_resource_record_key_created_at_index on admin.content_revisions (resource, record_key, created_at);

create table if not exists admin.content_meta (
  resource varchar(60) not null,
  record_key varchar(191) not null,
  status varchar(20) not null default 'published',
  created_by bigint references admin.users(id) on delete set null,
  created_by_name varchar(120),
  updated_by bigint references admin.users(id) on delete set null,
  updated_by_name varchar(120),
  created_at timestamp(0) without time zone,
  updated_at timestamp(0) without time zone,
  primary key (resource, record_key)
);

create table if not exists admin.audit_runs (
  id bigserial primary key,
  type varchar(20) not null,
  status varchar(20) not null default 'running',
  summary jsonb,
  findings jsonb,
  error text,
  user_id bigint references admin.users(id) on delete set null,
  started_at timestamp(0) without time zone not null default CURRENT_TIMESTAMP,
  finished_at timestamp(0) without time zone
);
create index if not exists audit_runs_type_started_at_index on admin.audit_runs (type, started_at);

create table if not exists admin.backup_runs (
  id bigserial primary key,
  trigger varchar(20) not null default 'manual',
  scope jsonb not null,
  status varchar(20) not null default 'running',
  path varchar(300),
  size bigint,
  sha256 varchar(64),
  row_counts jsonb,
  error text,
  user_id bigint references admin.users(id) on delete set null,
  created_at timestamp(0) without time zone not null default CURRENT_TIMESTAMP,
  verified_at timestamp(0) without time zone,
  deleted_at timestamp(0) without time zone
);
create index if not exists backup_runs_created_at_index on admin.backup_runs (created_at);

create table if not exists admin.error_logs (
  id bigserial primary key,
  level varchar(20) not null default 'error',
  exception varchar(191) not null,
  message varchar(500) not null,
  location varchar(300),
  method varchar(10),
  path varchar(300),
  user_id bigint references admin.users(id) on delete set null,
  created_at timestamp(0) without time zone not null default CURRENT_TIMESTAMP
);
create index if not exists error_logs_created_at_index on admin.error_logs (created_at);

create or replace function admin.activity_logs_append_only() returns trigger language plpgsql as $$
begin
  if tg_op = 'UPDATE' and new.user_id is null and old.user_id is not null
     and (to_jsonb(new) - 'user_id') = (to_jsonb(old) - 'user_id') then
    return new;
  end if;
  raise exception 'Activity log entries cannot be changed or deleted';
end $$;
drop trigger if exists activity_logs_append_only on admin.activity_logs;
create trigger activity_logs_append_only before update or delete on admin.activity_logs
  for each row execute function admin.activity_logs_append_only();

alter table public.blog_posts add column if not exists publish_at timestamptz;
alter table public.case_studies add column if not exists publish_at timestamptz;
alter table public.news_posts add column if not exists publish_at timestamptz;
alter table public.whitepapers add column if not exists publish_at timestamptz;
alter table public.job_openings add column if not exists publish_at timestamptz;
alter table public.team_members add column if not exists publish_at timestamptz;
alter table public.pages add column if not exists publish_at timestamptz;
alter policy "Public can read published blog posts" on public.blog_posts using (published and (publish_at is null or publish_at <= now()));
alter policy "Public can read published case studies" on public.case_studies using (published and (publish_at is null or publish_at <= now()));
alter policy "Public can read published news" on public.news_posts using (published and (publish_at is null or publish_at <= now()));
alter policy "Public can read published whitepapers" on public.whitepapers using (published and (publish_at is null or publish_at <= now()));
alter policy "Public can read published jobs" on public.job_openings using (published and (publish_at is null or publish_at <= now()));
alter policy "Public can read published team" on public.team_members using (published and (publish_at is null or publish_at <= now()));
alter policy "Public can read published pages" on public.pages using (published and (publish_at is null or publish_at <= now()));

grant select, insert, update, delete on admin.notifications, admin.content_revisions, admin.content_meta, admin.audit_runs, admin.backup_runs, admin.error_logs to achivex_admin;
grant usage, select on all sequences in schema admin to achivex_admin;

insert into admin.migrations (migration, batch)
select '2026_10_23_000001_admin_phase1', 5
where not exists (select 1 from admin.migrations where migration = '2026_10_23_000001_admin_phase1');
