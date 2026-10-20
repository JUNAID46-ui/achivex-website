-- Run once in the Supabase SQL editor (already applied to project pktnqerzatrdndhbtsqp).
-- After a visitor sends a contact message or guide request, Postgres pings the admin, which emails
-- the owner. pg_net sends the request in the background, so the visitor's form never waits for it.
-- Change the URL if the admin moves to another domain.
create extension if not exists pg_net with schema extensions;

create or replace function public.notify_admin_inbox() returns trigger
language plpgsql security definer set search_path = '' as $$
begin
  perform net.http_post(
    url := 'https://achivex-admin.vercel.app/hooks/inbox',
    body := '{}'::jsonb,
    headers := '{"Content-Type": "application/json"}'::jsonb,
    timeout_milliseconds := 10000
  );
  return null;
end;
$$;
revoke all on function public.notify_admin_inbox() from public, anon, authenticated;

drop trigger if exists notify_admin_inbox on public.contact_submissions;
create trigger notify_admin_inbox after insert on public.contact_submissions
  for each statement execute function public.notify_admin_inbox();

drop trigger if exists notify_admin_inbox on public.whitepaper_requests;
create trigger notify_admin_inbox after insert on public.whitepaper_requests
  for each statement execute function public.notify_admin_inbox();
