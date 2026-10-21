# AchiveX Admin: phase 1 report

Branch `claude/security-hardening`, commits `acc8cdc` to the commit that adds this file. 101 files changed (52 new, 49 changed).

**Status: built and tested locally, not deployed.** One database migration must be approved and applied first (section 4).

## 1. Features already present and reused

| Existing piece | Reused for |
|---|---|
| `App\Admin\Resource` and `ResourceController` (generic content CRUD) | Revisions, workflow states, checks, preview, safer bulk actions for every content type |
| `User::canAccess()` roles and `EnsureArea` middleware | All new pages and routes |
| `SeoIndex`, `SeoAnalyzer`, `Sitemap`, `SiteRoutes` | SEO audit, AI visibility, link checker |
| `Backup` service (JSON format, parse, transactional restore) | Stored, verified backups and restore preview |
| `SupabaseStorage` (client, signed uploads) | Private `backups` bucket and large file restores |
| `ActivityLog`, `InboxNotifier`, `Csv`, `TwoFactor`, `AppSetting`, `SiteSetting` | Activity, mail status, CSV exports, Security Center, settings |
| Admin layout, Tailwind components, Alpine.js | All new views (no redesign) |

Not found in the code: a global search. It was listed as existing; it does not exist today, and it was not added in this phase.

## 2. New features

- **Notification center**: bell with unread count, list, read and unread, archive, severity filter. Users only ever see their own. Links can only point inside the admin.
- **Activity log**: role, module, record id, success or failure, filtered context, CSV export, search and date filters. Rows cannot be edited or deleted. The app has no route for it and a database trigger blocks it. Failed sign ins, wrong two-step codes, sign outs, role changes, password and two-step changes, settings, publishing, restores and exports are recorded.
- **Content management** (blog, case studies, news, whitepapers, careers, team, milestones, locations, pages):
  - A revision is saved before every change and delete, with compare and restore. Deleted items can be restored as drafts.
  - Draft, review, scheduled, published and archived states. Authors cannot publish. Scheduled items go live at the exact time through the read policy.
  - Created by and last changed by, with timestamps.
  - Checks: missing image, missing meta description, noindex on published items, address clashes with redirects or pages, broken internal links.
  - Private preview of drafts.
  - Bulk publish, draft, review, archive and delete, each logged per item and permission checked.
- **SEO audit**: missing, long and duplicate titles and descriptions, broken internal links, links through redirects, redirect chains and loops, redirects to missing pages, canonical and noindex conflicts, sitemap errors, robots rules that block sitemap pages, images without alt text, important pages with few links, and a comparison with what the live site serves.
- **AI visibility**:
  - Checks the raw HTML of key pages, structured data in the HTML, and access for 12 documented crawlers in robots.txt.
  - Checks llms.txt, organization and entity data, and indexing of important pages.
  - Content answer readiness and topic depth are reported as recommendations.
  - There is an llms.txt editor with a draft built from site content.
  - Citations, rankings and crawler visits are not shown, because no data source for them is connected.
- **Audit history**: every SEO, AI and security run is stored with summary, severity counts, change since the last run, and CSV or JSON download. Every finding is labelled Verified or Recommendation.
- **Security Center** (administrators only):
  - Sign in history and failed attempts, with suspicious patterns flagged: repeated failures, many failures from one IP, success after failures, a new IP.
  - Users with role, two-step status, last sign in and sessions. An administrator can sign a user out everywhere after entering their own password.
  - Recent sensitive actions.
  - A route authorization check, configuration warnings, a masked scan of deployed code for credentials, and package vulnerabilities from OSV.dev.
  - Your own sessions, with a button to sign out other devices.
- **System Health** (administrators only):
  - Database connection and size, file storage use against limits with warning and critical thresholds, backups, the daily task, application errors, jobs, cache and email.
  - Supabase Storage and the public website are checked on demand.
  - Shows the Laravel and PHP versions, environment and deployed commit.
  - Anything that cannot be measured shows as Unknown.
  - Application errors are now kept in the database with SQL values and credentials removed.
- **Backups**:
  - Stored in a private Supabase bucket and verified by reading the file back (SHA-256 and row counts). A backup is not called successful before that.
  - History with size and hash, one minute download links, a retention setting and a daily scheduled backup.
  - Restore shows a per table preview, needs the word RESTORE typed in, and saves a safety backup of the current data first.
  - Large backup files upload straight to private storage. The old 4 MB limit applies only when storage is not set up.
  - Failures send notifications. The page lists what is and is not in a backup.
- **Daily task**: `/hooks/daily` through Vercel Cron. It sends health notifications, makes the scheduled backup, applies retention and cleans up the error log. Each step's result is saved. It runs at most once every 20 hours.
- **Website build**: takes robots.txt, sitemap.xml and llms.txt from the admin, and keeps the bundled files if the admin cannot be reached.
- **UX**: unsaved changes warning on content and llms.txt forms, loading state on save buttons, confirmations on every destructive action, empty states, filters and search on the new tables, UTC times with relative tooltips.

## 3. Files

New services:
- `app/Services/{Notifier,Revisions,ContentWorkflow,ContentHealth,SiteLinks}.php`
- `app/Services/Audit/*` (Finding, Robots, LiveSite, Corpus, SeoAudit, AiVisibilityAudit, AuditRunner)
- `app/Services/Security/*` (SecurityScan, SecretScanner, DependencyCheck, Sessions)
- `app/Services/Health/{HealthCheck,DailyTasks}.php`
- `app/Services/Backup/{PrivateStorage,BackupManager}.php`

New models:
- `AdminNotification`, `ContentRevision`, `ContentMeta`, `AuditRun`, `BackupRun`, `ErrorLog`
- `Content/Publishable` (trait)

New controllers:
- `Admin/{Notification,Audit,Security,Health}Controller`
- `DailyHookController`

New views:
- `notifications/`, `security/`, `health/`
- `backup/preview`
- `seo/{audit,ai-visibility,partials/audit-findings}`
- `resources/{revisions,deleted,preview}`
- `pages/partials/revisions`

Changed:
- `ResourceController`, `PageController`, `BackupController`, `AuthController`, `ProfileController`, `ActivityController`, `SettingsController`, `CustomCodeController`, `SeoController`, `PublicSeoController`
- `ActivityLog`, `User`, the content models, `SeoIndex`, `SupabaseStorage`
- `routes/web.php`, `config/admin.php` (menu), `config/filesystems.php`, `config/services.php`, `bootstrap/app.php`
- `vercel.json` (cron)
- Layout, icons, resource views, profile, SEO overview
- `public/js/admin.js`, `public/css/admin.css`
- `website/scripts/export-routes.mjs`, `.gitignore`

Tests:
- `tests/PostgresTestCase.php`, `tests/SqliteTables.php`
- 6 new feature test files
- `tests/fixtures/schema.sql` regenerated from the production table layout

## 4. Database migration and configuration (needs approval)

`admin/database/migrations/2026_10_23_000001_admin_phase1.php` is additive only. Nothing is dropped and no existing data changes.

- New tables in the `admin` schema: `notifications`, `content_revisions`, `content_meta`, `audit_runs`, `backup_runs`, `error_logs`.
- New nullable columns on `admin.activity_logs`, plus an append only trigger. The trigger still allows the user id to be cleared when a user is deleted.
- `publish_at timestamptz` (nullable) on `public.blog_posts`, `case_studies`, `news_posts`, `whitepapers`, `job_openings`, `team_members`, `pages`.
- The website read policy on those tables changes from `published` to `published and (publish_at is null or publish_at <= now())`. Existing rows have `publish_at` null, so the website shows exactly what it shows today.

It was tested on a local PostgreSQL 16 copy of the production layout: up, rollback and up again, the scheduled policy as `anon`, the append only trigger, and deleting a user who has log rows.

Configuration:
- `vercel.json` (admin) adds a daily cron at 06:17 UTC.
- Optional: set `CRON_SECRET` on the admin Vercel project so only Vercel can call `/hooks/daily`.
- `config/filesystems.php` turns off Laravel's private local file routes, which nothing used.

After deploy, the owner clicks "Set up private backup storage" once, on the Backup page.

## 5. Endpoints

All are under `/admin` and need sign in unless marked public. The role needed is in brackets.

- Notifications (any role, own notifications only): `GET notifications`, `POST notifications/read-all`, `GET notifications/{id}/open`, `POST notifications/{id}`
- Activity (admin): `GET activity/export`
- Content (role of the content type):
  - `GET r/{resource}/deleted`, `POST r/{resource}/deleted/{revision}`
  - `GET r/{resource}/{id}/revisions`, `POST r/{resource}/{id}/revisions/{revision}`
  - `GET r/{resource}/{id}/preview`
  - `POST pages/revisions/{revision}` (builder)
  - Changed: `POST/PUT r/{resource}` now need `workflow_status` for content that can be published. `bulk` accepts `publish|draft|review|archive|delete`.
- SEO (seo):
  - `GET seo/audit`, `GET seo/ai-visibility`
  - `POST seo/audits/{seo|ai}`, `GET seo/audits/{run}/download/{csv|json}`
  - `POST seo/llms`, `GET seo/llms/draft`
- Security (admin): `GET security`, `POST security/scan`, `POST security/users/{user}/sessions`
- Profile (any role): `POST profile/sessions`
- Health (admin): `GET health`, `POST health/thresholds`
- Backup (admin):
  - `POST backup/setup`, `POST backup/runs`, `POST backup/settings`
  - `POST backup/runs/{run}/verify`, `GET backup/runs/{run}/download`, `POST backup/runs/{run}/delete`
  - `GET backup/runs/{run}/preview`, `POST backup/runs/{run}/restore`
  - `POST backup/upload/sign`, `POST backup/upload/{run}/complete`
  - Changed: `POST backup/restore` (small file fallback) now needs the word RESTORE.
- Public:
  - `GET /llms.txt`, which returns 404 until one is written.
  - `GET|POST /hooks/daily`, which is throttled, takes a bearer token when `CRON_SECRET` is set, and does real work at most once every 20 hours.

## 6. Tests actually run

- **PHPUnit**: 76 tests, 378 assertions, all passing. Run on PHP 8.3 against a local PostgreSQL 16 built from the production layout (`TEST_PG_HOST=127.0.0.1`).
  - New: notifications and activity 7, content 12, audits 6, Security Center 7, backup and health 9.
  - Existing tests were updated for the new activity columns and the RESTORE confirmation.
- **Browser** (Playwright, Chromium), against a local admin on Postgres:
  - 20 admin pages returned 200 with no JavaScript errors.
  - SEO audit, AI audit and security check were run from the UI.
  - An editor gets 403 on Security, Health and Backup.
- **Website**: `npm run build` passes. The export script falls back to bundled files when the admin is unreachable.
- **Not tested here**, because this sandbox cannot reach them:
  - Live OSV.dev (tested with a faked response)
  - Real Supabase Storage (tested with an in-memory fake of its API)
  - Vercel Cron
  - The production database with the new migration

## 7. Security issues found and fixed

1. The robots.txt and sitemap edited in the admin never reached the live website. The site built its own from bundled data. The build now takes them from the admin, and the audit flags any difference.
2. Failed sign ins were not recorded, so guessing was invisible. They are now recorded, without the password.
3. Activity log rows could be changed by the app's database role. They are now append only, enforced by a trigger.
4. Role changes were not logged at all. They are now logged in the model, so every path is covered.
5. Deleting content was permanent. A copy is now kept and can be restored.
6. Restore had no preview and no undo. It now has a preview, a typed confirmation and an automatic safety backup.
7. Backups were never verified. They are now checked with SHA-256 and row counts.
8. Laravel 12 enabled `/storage/{path}` routes for the private local disk. Nothing used them, so they are now turned off.
9. Application errors were only visible in Vercel logs. They are now stored, scrubbed of SQL values and credentials.

## 8. Incomplete or external

- **Biggest AI visibility gap**: the site is rendered in the browser, so crawlers that do not run JavaScript see an almost empty page. Pre-rendering is an architecture change and was left for a later phase. The audit reports it as a verified finding.
- **Custom JavaScript** is still blocked by the website Content Security Policy, waiting on the owner's decision.
- **Supabase Free plan**: 1 GB storage, 50 MB per file. Large backup files and media need the Pro plan.
- **Vercel Hobby plan**: cron runs at most once a day.
- **Storage use** is counted from admin records. Files uploaded outside the admin are not included.
- **Global search** is not present.
- **Branch**: not yet merged into `main`.

## 9. Manual testing after deploy

1. Sign in as an administrator, open the bell, then Notifications.
2. Content:
   - Edit a blog post, change a word and save.
   - Open Revisions, compare, then restore.
   - Set the status to Scheduled with a time 5 minutes ahead and check that the post appears on the website only after that time.
3. Delete a test post, open "Recently deleted" and restore it. It comes back as a draft.
4. SEO:
   - Under SEO, open SEO Audit, click Run audit now and download the CSV.
   - Open AI Visibility, run the audit, build an llms.txt draft and save it.
5. Security Center: run the security check. Sign in with a wrong password once and check that it shows up.
6. Tools:
   - Open System Health and click Run live checks.
   - Open Backup and Restore, click Set up private backup storage, then Save and verify.
   - Use Restore… to open the preview, then go back without restoring.
7. Sign in as an editor and confirm that Security Center, System Health and Backup show 403.
8. Publish the website again, then open `/robots.txt`, `/sitemap.xml` and `/llms.txt` on it.

## 10. Checklist for next week's generalization

- [ ] Decide the tenancy model: one database per site, or a `site_id` on every table plus row level security per site.
- [ ] Move hard coded AchiveX values (`config/admin.php` site URLs, email brand, important page list, crawler list, menu) into per site settings.
- [ ] Make `Resource` definitions data driven per site, or registerable by plugin.
- [ ] Replace `SiteRoutes` (`site-routes.json` from the React build) with a connector interface.
- [ ] Scope audits, backups, notifications, revisions and health per site (each table gains `site_id`).
- [ ] Per site storage buckets and backup retention.
- [ ] A permission model with per site roles in addition to the global roles.
- [ ] Pre-rendering or server rendering for the website (biggest AI and SEO item).
- [ ] Global search across content, media, inbox and settings.
- [ ] Load tests and the per request time limit on Vercel for larger sites (audits fetch a few URLs in parallel within about 5 seconds).
