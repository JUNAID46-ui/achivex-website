-- AchiveX: blocked IP addresses (same as migration 2026_10_26_000001_create_blocked_ips_table).
-- Additive only: creates one new table and records the migration. Safe to run more than once.
create table if not exists admin.blocked_ips (
  id bigserial primary key,
  ip varchar(64) not null,
  attempts integer not null default 0,
  emails json,
  user_agent varchar(300),
  reason varchar(200),
  first_attempt_at timestamp(0) without time zone,
  blocked_at timestamp(0) without time zone not null,
  expires_at timestamp(0) without time zone,
  unblocked_at timestamp(0) without time zone,
  unblocked_by varchar(120),
  notified_at timestamp(0) without time zone,
  created_at timestamp(0) without time zone,
  updated_at timestamp(0) without time zone
);
create index if not exists blocked_ips_ip_index on admin.blocked_ips (ip);
alter table admin.blocked_ips enable row level security;

insert into admin.migrations (migration, batch)
select '2026_10_26_000001_create_blocked_ips_table', coalesce(max(batch), 0) + 1 from admin.migrations
where not exists (select 1 from admin.migrations where migration = '2026_10_26_000001_create_blocked_ips_table');
