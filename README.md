# AchiveX LLC website and admin panel

**AchiveX LLC** is a Wyoming based marketing and software solutions company. This repository holds its public website and the admin panel used to manage it. Both read and write the same Supabase database.

## Folders

| Folder | What it is | Built with |
| --- | --- | --- |
| `website/` | The public website | React, Vite, Tailwind CSS, Framer Motion |
| `admin/` | Admin panel: pages, page builder, posts, media, inbox, SEO, analytics | Laravel 11, PHP 8.2+, Alpine.js |
| `website/supabase/` | Database migrations and seed data | Postgres (Supabase) |

## Run the website

```bash
cd website
cp .env.example .env
npm install
npm run dev
```

Open http://localhost:5173. The `.env.example` file already holds the public Supabase URL and publishable key.

## Deploy the website on Vercel

The repository is ready for Vercel. No settings or environment variables are needed.

1. On vercel.com click **Add New**, then **Project**, and import this repository.
2. Leave **Root Directory** as it is (the repository root). The `vercel.json` file tells Vercel how to build the site from the `website` folder.
3. Click **Deploy**.

Optional: if the site will not live at www.achivex.com, add an environment variable `SITE_URL` (for example `https://achivex.vercel.app`) so sitemap.xml and robots.txt use the right address.

The admin panel is PHP and does not run on Vercel. Run it on your computer or on a PHP host, and set `SITE_URL` in its `.env` to the Vercel address so the Page Builder preview shows the live site.

## Run the admin panel

Needs PHP 8.2 or newer with the `pdo_pgsql` extension, and Composer.

```bash
cd admin
composer install
cp .env.example .env
php artisan key:generate
```

Open `.env` and fill in:

- `DB_PASSWORD`: the Supabase database password.
- `ADMIN_EMAIL` and `ADMIN_PASSWORD`: the first administrator account.

Then run:

```bash
php artisan achivex:install
php artisan serve
```

Open http://127.0.0.1:8000/admin.

The email server and the image storage key are set inside the admin, under **Settings**.

## What is not in this repository

- `.env` files with passwords and keys.
- `admin/vendor` (installed by `composer install`) and `node_modules` (installed by `npm install`).
