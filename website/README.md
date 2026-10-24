# AchiveX LLC Website

React (Vite), React Router, Tailwind CSS, Framer Motion and Lucide React.

## Run it

```bash
npm install
npm run dev       # local development
npm run build     # production build in /dist
npm run preview   # preview the production build
```

The site is a single page app. On your host, send every path to `index.html` (a `public/_redirects` file is included for Netlify. For Vercel or Apache, add the matching rewrite rule).

## Admin panel

A separate Laravel admin panel (`achivex-admin`) edits everything below: content, the home page builder, SEO, redirects, media, inbox, analytics and users. See its README for setup.

The website logs page views and missing pages (404s) to the database for the admin's Analytics and 404 Monitor, and follows redirects created in the admin.

## Database (Supabase)

The site is connected to the Supabase project **achivex-website** (`pktnqerzatrdndhbtsqp`).
Connection values live in `.env` (copy `.env.example` if it is missing). They are publishable keys and safe for the browser.

Edit content without touching code: open the Supabase dashboard, go to **Table Editor** and change rows.

| Table | What it controls |
| --- | --- |
| `hero_stats` | The three figures on the home page hero cards |
| `blog_posts` | Blog articles (set `published` to false to hide one) |
| `case_studies` | Case studies |
| `news_posts` | Newsroom announcements |
| `whitepapers` | Guides. Put the PDF link in `file_url` |
| `job_openings` | Careers page. When empty, the page invites general applications |
| `team_members` | Leadership page. When empty, the team grid is hidden |
| `milestones` | Accomplishments timeline |
| `locations` | Global Presence page |
| `site_settings` | Contact details, social links, SEO defaults, schema and verification codes |
| `page_sections` | Home page sections from the Page Builder |
| `seo_meta` | Per page SEO title, description, keyword and noindex |
| `redirects` | Redirects followed by the website |
| `page_views`, `not_found_log` | Analytics and 404 log |
| `contact_submissions` | Messages sent from the contact form (read them here) |
| `whitepaper_requests` | People who requested a guide (send them the file) |

The schema is in `supabase/migrations/` and the starting content in `supabase/seed.sql`.
If the database cannot be reached, the site falls back to the files in `src/data/`.

## Where to edit content

| What | File |
| --- | --- |
| Phone, email, socials, Google profile, map | `src/data/site.js` |
| Service categories (menu order) | `src/data/categories.js` |
| All 20 service pages | `src/data/services.js` |
| Industries dropdown and pages | `src/data/industries.js` |
| Alliances (remove any that do not apply) | `src/data/alliances.js` |
| Values, milestones, ESG, leadership, locations, job openings | `src/data/about.js` |
| Case studies (samples, replace with real ones) | `src/data/caseStudies.js` |
| Newsroom (placeholders) | `src/data/news.js` |
| Whitepapers and eBooks | `src/data/whitepapers.js` |
| Blog posts | `src/data/blogs.js` |
| Featured items in mega menus | `src/data/menus.js` |

Adding an item to any of these arrays creates its page, menu link and search entry automatically.

## Before launch

Add leadership profiles, job openings and the office address in the database when they are ready. Have the Privacy Policy and Terms of Service reviewed by an attorney.

## Images

All images are Unsplash placeholder URLs built with `img(id)` in `src/data/images.js`. Replace an id, or pass your own URL, to change an image. If a URL fails to load, a neutral grey block is shown instead.

## Forms

The contact form saves to `contact_submissions` and the guide download form saves to `whitepaper_requests`. Check these tables in Supabase, or add a Database Webhook there to get an email for each new row.

## Colors

`tailwind.config.js` replaces Tailwind's default palette with the brand colors only: `navy` #082032, `slate` #334756, `accent` #F0A500, `ink` #000000, `mist` #EEF1F3 (a light tint of slate) and `white`.

## Build and deploy (Next.js)

The website is a Next.js app that exports every page to plain HTML (`output: 'export'`), so it runs
on any host. Search engines get each page's full text and title without running JavaScript.

```bash
cd website
npm ci
npm run build        # writes site-routes.json for the admin, then builds into website/out
```

The build reads the latest content from Supabase. If the build machine cannot reach it, set
`CONTENT_SNAPSHOT=/path/to/content.json` or the bundled content is used. In the browser the site
still loads the newest content from the database, so admin changes show without a rebuild; a rebuild
only refreshes the prerendered HTML that search engines read.

**Hostinger (or any Apache host):** upload everything inside `website/out` (including the hidden
`.htaccess` file) to `public_html`. Addresses without their own file fall back to `404.html`, which
opens posts published after the last build and shows the 404 page for anything else.

**Vercel:** the root `vercel.json` runs the build and serves `website/out`.
