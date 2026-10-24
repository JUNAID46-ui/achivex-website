# Moving AchiveX to Hostinger

The website (www.achivex.com) and the admin (admin.achivex.com) move from Vercel to Hostinger.
The database stays on Supabase for now. Vercel stays on for 2 days after the DNS switch, so we can go back if anything is wrong.

## What is already prepared in the code

| Part | What it does |
|---|---|
| `.github/workflows/website-hostinger.yml` | Builds the website on GitHub and uploads only changed files to Hostinger by FTP. Runs on every website change, on every content change in the admin, and by hand. |
| `.github/workflows/admin-hostinger.yml` | Installs the admin's PHP packages and uploads the admin by FTP. Never uploads `.env`. |
| `admin/.htaccess.hostinger` | Becomes the admin's `.htaccess`. Sends every request to Laravel's `public` folder, so `.env` and the code can never be opened in a browser. |
| Instant publish (`admin/app/Services/InstantPublish.php`) | When a post or page is saved, its page file is written straight into the website folder with its own title, description and canonical link. It answers with status 200 at once. The full build replaces it a few minutes later. sitemap.xml, robots.txt and llms.txt are written at the same time. |
| Publish button (admin, Cache page) | Writes new pages at once and starts the full build. |
| `php artisan site:publish` and `php artisan site:daily` | Cron jobs that replace Vercel Cron. |
| `website/public/.htaccess` | HTTPS, addresses with a slash, 404 page, security headers, caching. Copied into every build. |

## Step 1. Hostinger hPanel

1. **Website:** Websites, Add website, use `achivex.com`. Its folder is `domains/achivex.com/public_html`. Delete the default files Hostinger puts there (`default.php`, `index.php`).
2. **Admin:** add `admin.achivex.com` as its **own website** so it gets its own folder, `domains/admin.achivex.com/public_html`. Do not put the admin inside the website's folder: the website's `.htaccess` would then apply to the admin too.
3. **PHP for the admin:** Advanced, PHP Configuration: PHP **8.3**, and turn on the `pgsql` and `pdo_pgsql` extensions (the admin talks to Supabase Postgres).
4. **FTP:** Files, FTP Accounts. Note the host, user and password. One account that can reach both folders is enough.
5. Note your home folder path, for example `/home/u123456789`. It is shown in File Manager and in SSH.

## Step 2. GitHub

1. Bring `main` up to date with the working branch (`claude/security-hardening`). GitHub runs the rebuild trigger from `main` only.
2. Settings, Secrets and variables, Actions, New repository secret:

| Secret | Example |
|---|---|
| `FTP_SERVER` | `ftp.achivex.com` or the IP from hPanel |
| `FTP_USERNAME` | from hPanel |
| `FTP_PASSWORD` | from hPanel |
| `FTP_SITE_DIR` | `domains/achivex.com/public_html/` |
| `FTP_ADMIN_DIR` | `domains/admin.achivex.com/public_html/` |

The folders are relative to where the FTP account starts. If the account opens inside `public_html` already, shorten them.

3. Make a token for the admin's rebuild trigger: GitHub, Settings, Developer settings, Fine-grained tokens. Repository: only `achivex-website`. Permission: **Contents: Read and write**. Keep it for step 3.

## Step 3. Admin

1. GitHub, Actions, **Admin to Hostinger**, Run workflow. Wait until it is green.
2. In hPanel File Manager, create `.env` in `domains/admin.achivex.com/public_html/`. Copy every value from the admin's Vercel project (the deploy session can list them), then change or add these:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.achivex.com
APP_KEY=            same key as on Vercel, or saved 2FA secrets and encrypted settings stop working
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
LOG_CHANNEL=daily
SITE_PUBLIC_URL=https://www.achivex.com
SITE_DEPLOY_HOOK=https://api.github.com/repos/JUNAID46-ui/achivex-website/dispatches
SITE_DEPLOY_TOKEN=  the token from step 2
SITE_PUBLIC_PATH=/home/u123456789/domains/achivex.com/public_html
```

Keep `DB_*`, `MAIL_*`, `CRON_SECRET` and the other values exactly as on Vercel.

3. hPanel, Advanced, Cron Jobs. Add two (use the PHP path hPanel shows, often `/usr/bin/php`):

```
*/5 * * * *   cd /home/u123456789/domains/admin.achivex.com/public_html && /usr/bin/php artisan site:publish
17 6 * * *    cd /home/u123456789/domains/admin.achivex.com/public_html && /usr/bin/php artisan site:daily
```

## Step 4. Website

GitHub, Actions, **Website to Hostinger**, Run workflow. The first upload sends every file and takes a few minutes; later runs only send changed files.

## Step 5. Check before switching DNS

Use the temporary address hPanel gives each website (or a hosts file entry) and check:

- [ ] Home, a service page, a blog post and the 404 page open, with no errors in the browser console
- [ ] `/about-us` goes to `/about-us/` and `http://` goes to `https://`
- [ ] Admin login works, including 2FA if it is on
- [ ] Save a test blog post as published: its page opens at once, then delete it
- [ ] Cache page shows "Instant publishing is on", and Publish starts a build in GitHub Actions

## Step 6. DNS switch

1. Point the domain to Hostinger: either change the nameservers to Hostinger's, or set the `A` records for `achivex.com`, `www` and `admin` to the Hostinger IP from hPanel.
2. hPanel, Security, SSL: install the free SSL certificate for `achivex.com`, `www.achivex.com` and `admin.achivex.com` once DNS has moved.
3. Update the database hook that emails new contact messages (Claude can run this):

```sql
-- in public.notify_admin_inbox(): replace the address
url := 'https://admin.achivex.com/hooks/inbox',
```

4. Check again: contact form message arrives in the admin inbox and by email, sitemap.xml and robots.txt open, Google Search Console shows no new errors.

## Step 7. Two days later

If everything works, turn off both Vercel projects (website and admin). Until then, switching the DNS back to Vercel undoes the move.

Supabase stays. Moving the database is a separate step, after the CMS work.
