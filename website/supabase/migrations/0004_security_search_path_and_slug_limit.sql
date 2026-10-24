-- Security check (October 2026): fix the function search path the Supabase advisor flagged,
-- and limit the whitepaper slug that visitors send, like the other form fields.
alter function admin.activity_logs_append_only() set search_path = admin, pg_catalog;
alter table public.whitepaper_requests add constraint whitepaper_requests_slug_length check (whitepaper_slug is null or char_length(whitepaper_slug) <= 200);
