# AchiveX Admin: phase 1 feature plan

Scope: the existing AchiveX admin (Laravel 12 + Blade + Alpine, Supabase Postgres, Vercel). No multi-site work, no framework or database change, no paid services.

## What the inspection found

| Area | Present today | Gap |
|---|---|---|
| Content (blog, case studies, news, whitepapers, careers, team, milestones, locations, pages) | Generic `Resource` CRUD, `published` switch, bulk publish/unpublish/delete, SEO score per record | No revisions, no review/scheduled/archived states, no attribution, no completeness or broken link warnings, no preview of unpublished content, bulk actions log one line only |
| SEO | Overview, per page scores, appearance, sitemap, robots, redirects, 404 monitor, local schema, webmaster codes | The live website builds its own `robots.txt` and `sitemap.xml` from bundled data, so the admin robots and sitemap settings never reach the live site. No redirect chain/loop, broken link, canonical, alt text or indexability checks. No audit history |
| AI visibility | None | Everything |
| Security | Login throttle, 2FA, roles, security headers, audit report in the repo | Failed logins are not recorded, no in-app security view |
| Health | None | Everything. Production logs go to Vercel stderr, which the app cannot read |
| Backup | JSON download and upload restore (4 MB upload limit on Vercel) | No stored history, no integrity hash, no preview, no retention, no failure alerts |
| Notifications | Inbox emails only | No in-app notification center |
| Activity log | user, action, type, label, description, ip | No role, module, record id, success/failure, context. Rows can be changed by the app role |
| Global search | Not present (only per table search) | Out of scope for this phase, noted |

The admin is server rendered Blade, not React. React is only the public website.

## Stages

1. **Foundation**: migrations for notifications, content revisions, content workflow, audit runs, backup runs, error log; richer activity log with an append only trigger.
2. **Notifications**: bell in the top bar, list page, read/archive, created by the events below.
3. **Content**: revisions with restore, workflow states (draft, review, scheduled, published, archived), attribution, completeness and broken link warnings, slug and path conflict warnings, admin side preview, safer bulk actions.
4. **SEO and AI visibility**: one site auditor that reads the database and fetches a small set of live URLs. Findings carry severity, URL, evidence, fix, and are marked Verified or Recommendation. History and CSV/JSON download. `llms.txt` editor. Website build pulls robots, sitemap and llms.txt from the admin.
5. **Security Center**: login and failed login history, suspicious patterns, roles and 2FA overview, sensitive actions, route authorization check, configuration warnings, secret scan of deployed files (masked), dependency check through OSV.dev (free), session controls.
6. **System health**: database, storage use against a configurable limit, failed jobs, daily task status, recent application errors (captured in the database, redacted), cache, backups, mail and storage integration status, version and environment.
7. **Backup**: backups stored in a private Supabase bucket with SHA-256, verification, history, retention, restore preview and typed confirmation, failure notifications. Daily scheduled backup through a Vercel cron.
8. **UX**: loading state on submit, unsaved changes warning, consistent confirms, empty states.

Scheduled publishing uses a `publish_at` column on the public content tables and a row level security condition, so posts go live at the exact time without a server task.

## Production changes that need approval before deploy

All are additive (new tables, new nullable columns, a changed read policy, an append only trigger). They are applied only after the owner approves.
