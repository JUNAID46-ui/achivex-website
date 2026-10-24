# Moving AchiveX to Hostinger

Everything runs on Hostinger: the website (www.achivex.com), the admin (admin.achivex.com), the code and the website builds. GitHub keeps a copy of the same code as a backup. GitHub has no password for Hostinger, and nothing on Hostinger can change GitHub.

The database stays on Supabase for now. Vercel stays on for 2 days after the DNS switch, so we can go back if anything is wrong.

**Plan needed:** Hostinger **Business** or higher (Cloud or VPS), because the website build needs Node.js and SSH.

## How it works after the move

| When | What happens |
|---|---|
| You save a post or page in the admin | The page is live **at once** with its own title, description and canonical link (instant publish). sitemap.xml and robots.txt update too. |
| Within a minute | A cron job builds the full website on Hostinger and copies only the changed files into the live folder. |
| You press **Publish** (admin, Cache page) | Same as above, straight away. |
| A build fails | The live website stays as it was. The Cache page shows the error. |
| Code or design changes | Pull the new code on Hostinger (step 7), then press Publish. |

## Folders on Hostinger

```
/home/u123456789/
  achivex/                                  the code (same as GitHub)
    admin/                                  Laravel admin, its .env lives here
    website/                                Next.js website, built here
  domains/achivex.com/public_html/          the live website (built files)
  domains/admin.achivex.com/public_html  -> link to achivex/admin/public
```

Replace `u123456789` with your own user name everywhere below. hPanel shows it in File Manager and SSH Access.

## Step 1. hPanel

1. **Website:** Websites, Add website, `achivex.com`. Delete the default files in its `public_html`.
2. **Admin:** add `admin.achivex.com` as its own website.
3. **PHP:** Advanced, PHP Configuration: PHP **8.3** for the admin, with `pgsql` and `pdo_pgsql` turned on.
4. **SSH:** Advanced, SSH Access: turn it on and note the login command.

## Step 2. Node.js

In SSH, check `node -v`. If it shows version 20.9 or newer, note the npm path with `which npm`.

If Node is missing or older, install it in your home folder:

```
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
source ~/.bashrc
nvm install 22
which npm        # note this path, for example /home/u123456789/.nvm/versions/node/v22.x.x/bin/npm
```

## Step 3. The code

Either option gives Hostinger its own copy of the code.

**Option 1, from GitHub (read only):**

```
ssh-keygen -t ed25519 -C hostinger -f ~/.ssh/github_achivex -N ""
cat ~/.ssh/github_achivex.pub
```

Add that key on GitHub: repository Settings, Deploy keys, Add, and leave "Allow write access" **off**. Then:

```
GIT_SSH_COMMAND="ssh -i ~/.ssh/github_achivex" git clone git@github.com:JUNAID46-ui/achivex-website.git ~/achivex
cd ~/achivex && git config core.sshCommand "ssh -i ~/.ssh/github_achivex"
```

The key can only read this one repository. GitHub gets nothing that opens Hostinger.

**Option 2, no link to GitHub at all:** download the code as a ZIP from GitHub, upload it in File Manager and extract it to `/home/u123456789/achivex`.

## Step 4. Admin

```
cd ~/achivex/admin
composer install --no-dev --optimize-autoloader
cd ~/domains/admin.achivex.com && rm -rf public_html && ln -s ~/achivex/admin/public public_html
```

Create `~/achivex/admin/.env`. Copy every value from the admin's Vercel project (the deploy session can list them), then change or add these:

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
SITE_DEPLOY_HOOK=
SITE_BUILD=server
SITE_SOURCE_PATH=/home/u123456789/achivex/website
SITE_PUBLIC_PATH=/home/u123456789/domains/achivex.com/public_html
SITE_NPM=/home/u123456789/.nvm/versions/node/v22.x.x/bin/npm
```

Keep `DB_*`, `MAIL_*`, `CRON_SECRET` and the other values exactly as on Vercel.

## Step 5. Cron jobs

hPanel, Advanced, Cron Jobs. Use the PHP 8.3 path hPanel shows (often `/usr/bin/php`):

```
* * * * *     cd /home/u123456789/achivex/admin && /usr/bin/php artisan site:build
*/5 * * * *   cd /home/u123456789/achivex/admin && /usr/bin/php artisan site:publish
17 6 * * *    cd /home/u123456789/achivex/admin && /usr/bin/php artisan site:daily
```

`site:build` builds the website when the admin asked for it. `site:publish` makes scheduled posts live. `site:daily` runs backups and checks.

## Step 6. First build and checks

1. Open the admin (hPanel's temporary address, or after DNS), log in, go to Cache and press **Publish**. Within a minute or two the card shows "Last build: ...". The first build takes longer because it installs the packages.
2. Check, before switching DNS (use the temporary address or a hosts file entry):
   - [ ] Home, a service page, a blog post and the 404 page open with no errors in the browser console
   - [ ] `/about-us` goes to `/about-us/`, and `http://` goes to `https://`
   - [ ] Admin login works, including 2FA if it is on
   - [ ] Save a test blog post as published: its page opens at once; a minute later the Cache page shows a new build; delete the post

## Step 7. Code updates later

When new code is on GitHub:

```
cd ~/achivex && git pull
cd admin && composer install --no-dev --optimize-autoloader
```

Then press **Publish** in the admin. With Option 2, upload the new files instead of `git pull`.

## Step 8. DNS switch

1. Point the domain to Hostinger: change the nameservers to Hostinger's, or set the `A` records for `achivex.com`, `www` and `admin` to the Hostinger IP from hPanel.
2. hPanel, Security, SSL: install the free certificate for `achivex.com`, `www.achivex.com` and `admin.achivex.com`.
3. Update the database hook that emails new contact messages (Claude can run this):

```sql
-- in public.notify_admin_inbox(): replace the address
url := 'https://admin.achivex.com/hooks/inbox',
```

4. Check again: a contact form message reaches the admin inbox and email, sitemap.xml and robots.txt open, and Google Search Console shows no new errors.

## Step 9. Two days later

If everything works, turn off both Vercel projects. Until then, pointing the DNS back to Vercel undoes the move.

GitHub stays as the copy of the code. Supabase stays until the CMS work moves the database.
