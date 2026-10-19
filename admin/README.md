# AchiveX Admin Panel

A WordPress style admin panel for the AchiveX website, built with PHP 8.2+ and Laravel 11.
It edits the same Supabase database the website reads, so every change shows on the site right away.

## What is inside

| Area | What you can do |
| --- | --- |
| Dashboard | Traffic chart, visitors, unread messages, SEO score, site health checks, quick draft, recent activity |
| Page Builder | Edit the home page like Elementor: drag sections to reorder, show or hide them, edit text, images, lists and the hero figures, with a live preview in desktop, tablet and mobile sizes |
| Content | Blog posts (with article sections), case studies, newsroom, whitepapers, careers, team, milestones, locations. Search, filters, bulk publish or delete |
| Media | Upload images and PDFs, copy links, set alt text, pick files from any form |
| Inbox | Contact form messages (read, star, reply, export CSV) and guide download requests |
| SEO | SEO overview score, every URL with score and fixes, focus keyword analysis with Google preview, search appearance, XML sitemap, robots.txt editor, redirects, 404 monitor, local SEO schema, Google and Bing verification |
| Analytics | Page views, visitors, top pages, referrers, devices, CSV export. No cookies or third party trackers |
| Settings | Business name, tagline, phone, email, location, social links |
| Users | Administrator, Editor and Author roles |
| Activity Log | Every change, who made it and when |

## Requirements

* PHP 8.2 or newer with the `pdo_pgsql`, `mbstring`, `fileinfo` and `gd` extensions turned on
* The `vendor` folder is included in the download, so Composer is only needed if you update packages

### Windows (XAMPP)

1. Install XAMPP with PHP 8.2 or newer from https://www.apachefriends.org
2. Open `C:\xampp\php\php.ini` and remove the `;` in front of these lines:
   `extension=pdo_pgsql`, `extension=pgsql`, `extension=fileinfo`, `extension=gd`
3. Add `C:\xampp\php` to your Windows PATH, then open a new Command Prompt and check `php -v`

## First time setup

1. **Database password.** In Supabase open the `achivex-website` project, then Project Settings, Database. Click "Reset database password" and copy the new password.
2. **Connection details.** In Supabase click "Connect" at the top, choose "Session pooler" and note the host and user.
3. **Create the .env file.** Copy `.env.example` to `.env` and fill in:
   * `DB_HOST` and `DB_USERNAME` from step 2 (user looks like `postgres.pktnqerzatrdndhbtsqp`)
   * `DB_PASSWORD` from step 1
   * `ADMIN_EMAIL` and `ADMIN_PASSWORD` for your own login (password at least 8 characters)
   * `SITE_URL` is where the website runs. Use `http://localhost:5173` while testing
4. **Install.** In this folder run:
   ```
   php artisan key:generate
   php artisan achivex:install
   ```
   This creates the private admin tables and your administrator account.
5. **Start.** Run `php artisan serve` (or double click `start.bat`) and open http://localhost:8000/admin

## Everyday use

* Run the website (`npm run dev` in the website folder) and the admin (`start.bat`) at the same time to use the live preview.
* Files uploaded to Media are served by the admin at `APP_URL/storage/...`. When you publish the admin online, set `APP_URL` to its real address so images work on the website.
* The SEO page list reads the website's fixed pages from `resources/site-routes.json`, and from `SITE_URL/site-routes.json` when the website is running. The website build updates that file automatically.

## Going live

Host the admin on any PHP host (for example a VPS, Laravel Forge, Ploi or shared hosting with PHP 8.2+):

1. Upload the folder and point the web root to `public`
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://admin.yourdomain.com`, `SITE_URL=https://www.achivex.com`
3. Run `php artisan achivex:install`, then `php artisan config:cache` and `php artisan route:cache`
4. Point `https://www.achivex.com/sitemap.xml` and `/robots.txt` to the admin's `/sitemap.xml` and `/robots.txt`, or download them from the SEO menu and upload them to the website

## Security

* Admin tables (users, sessions, media, activity) live in a private `admin` schema that the public website API cannot read.
* Logins are rate limited, passwords are hashed, and every form is protected against CSRF.
* Only administrators can manage users, settings and the activity log.
