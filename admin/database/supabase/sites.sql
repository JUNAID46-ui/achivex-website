-- AchiveX: Sites (same as migration 2026_10_25_000001_create_sites_table). Additive only.
-- Already applied to the main Supabase project; kept here for new installs.
create table if not exists admin.sites (
  id bigserial primary key,
  name varchar(120) not null,
  site_url varchar(500) not null,
  public_url varchar(500),
  db_host varchar(255) not null,
  db_port integer not null default 5432,
  db_database varchar(120) not null default 'postgres',
  db_username varchar(255) not null,
  db_password text not null,
  db_sslmode varchar(20) not null default 'require',
  notes text,
  status varchar(20) not null default 'new',
  status_message text,
  checked_at timestamp(0) without time zone,
  created_at timestamp(0) without time zone,
  updated_at timestamp(0) without time zone
);
alter table admin.sites enable row level security;
