<?php

namespace App\Services\Audit;

use App\Models\Content\BlogPost;
use App\Models\Content\SiteSetting;
use App\Services\SeoIndex;
use App\Services\SiteLinks;
use App\Services\Sitemap;

// Readiness of the website for AI search and answer engines. It checks things the admin can
// observe (settings, content, robots.txt, the HTML the site serves). It never claims citations,
// rankings or crawler visits: no data source for those is connected.
class AiVisibilityAudit
{
    // Crawlers documented by their operators. "purpose" is how the operator describes the bot.
    public const CRAWLERS = [
        'GPTBot' => 'OpenAI, model training',
        'OAI-SearchBot' => 'OpenAI, ChatGPT search',
        'ChatGPT-User' => 'OpenAI, user requested visits',
        'ClaudeBot' => 'Anthropic, model training',
        'Claude-SearchBot' => 'Anthropic, search',
        'Claude-User' => 'Anthropic, user requested visits',
        'PerplexityBot' => 'Perplexity, search',
        'Google-Extended' => 'Google, Gemini training and grounding control',
        'Googlebot' => 'Google Search, including AI Overviews',
        'Bingbot' => 'Bing, including Copilot answers',
        'Applebot-Extended' => 'Apple, AI training control',
        'CCBot' => 'Common Crawl, used by many AI datasets',
    ];

    public const IMPORTANT = ['/', '/services/', '/about-us/', '/contact-us/', '/insights/blogs/', '/insights/case-studies/'];

    public static function run(?array $live = null): array
    {
        $f = [];
        $base = LiveSite::base();
        $samplePost = BlogPost::live()->orderByDesc('published_on')->first();
        $paths = ['/', '/robots.txt', '/llms.txt', '/sitemap.xml'];
        if ($samplePost) {
            $paths[] = \App\Support\Permalinks::base('blogs').$samplePost->slug.'/';
        }
        $live ??= LiveSite::fetch($paths);
        $reachable = $live['/']['ok'] ?? false;

        // 1. What a crawler without JavaScript receives
        if ($reachable && ($live['/']['status'] ?? 0) === 200) {
            foreach (array_filter($paths, fn ($p) => ! str_contains($p, '.')) as $p) {
                $res = $live[$p] ?? null;
                if (! ($res['ok'] ?? false) || $res['status'] !== 200) {
                    continue;
                }
                $h = LiveSite::html($res['body']);
                if ($h['words'] < 100) {
                    $f[] = Finding::make('Page text is not in the HTML', 'Rendering', 'high', $base.$p,
                        "The HTML served before JavaScript runs contains {$h['words']} words. The page text is added by JavaScript in the browser.",
                        'Many AI crawlers do not run JavaScript, so they may see an almost empty page. Pre-render pages to static HTML at build time (planned work, not done in this phase).');
                }
                if ($h['jsonld'] === 0) {
                    $f[] = Finding::make('No structured data in the HTML', 'Structured data', 'medium', $base.$p,
                        'No application/ld+json found in the HTML served before JavaScript runs. The site adds Organization data with JavaScript only.',
                        'Include JSON-LD in the pre-rendered HTML so crawlers that do not run JavaScript can read it.');
                } elseif (! $h['jsonld_valid']) {
                    $f[] = Finding::make('Structured data is not valid JSON', 'Structured data', 'high', $base.$p, 'A JSON-LD block could not be parsed.', 'Fix the JSON-LD syntax.');
                }
            }
        } else {
            $f[] = Finding::make('Website could not be reached', 'Rendering', 'high', $base.'/', 'Request failed: '.($live['/']['error'] ?? 'HTTP '.($live['/']['status'] ?? '?')).'. Rendering, robots.txt and llms.txt checks on the live site were skipped.', 'Run the audit again when the website is online.');
        }

        // 2. AI crawler access, from the robots.txt the website serves
        $robotsLive = ($live['/robots.txt']['ok'] ?? false) && ($live['/robots.txt']['status'] ?? 0) === 200;
        $robotsText = $robotsLive ? $live['/robots.txt']['body'] : (string) (SiteSetting::group('robots')['content'] ?? '');
        $source = $robotsLive ? 'live robots.txt' : 'robots.txt saved in the admin (the live file could not be read)';
        $parsed = Robots::parse($robotsText);
        $access = [];
        foreach (self::CRAWLERS as $bot => $purpose) {
            $c = Robots::check($parsed, $bot, '/');
            $access[$bot] = $c + ['purpose' => $purpose];
            if (! $c['allowed']) {
                $f[] = Finding::make("{$bot} is blocked", 'Crawler access', in_array($bot, ['Googlebot', 'Bingbot', 'OAI-SearchBot', 'PerplexityBot', 'Claude-SearchBot'], true) ? 'high' : 'info', $base.'/robots.txt',
                    "{$c['rule']} in the {$source} applies to {$bot} ({$purpose}).",
                    'Allow it if you want the site to be used by this service. Blocking training bots is a business choice, not an error.');
            }
        }
        if (! $parsed['sitemaps']) {
            $f[] = Finding::make('robots.txt does not list the sitemap', 'Crawler access', 'low', $base.'/robots.txt', "No Sitemap line in the {$source}.", 'Add "Sitemap: '.rtrim((string) config('admin.site_public_url'), '/').'/sitemap.xml".');
        }

        // 3. llms.txt
        $llms = SiteSetting::group('llms');
        $llmsLive = ($live['/llms.txt']['ok'] ?? false) && ($live['/llms.txt']['status'] ?? 0) === 200 && ! str_contains(strtolower($live['/llms.txt']['type'] ?? ''), 'html');
        if (! $llmsLive) {
            $f[] = Finding::make('No llms.txt on the website', 'llms.txt', 'low', $base.'/llms.txt',
                $reachable ? 'The website does not serve /llms.txt (it returned '.($live['/llms.txt']['status'] ?? 'no response').($live['/llms.txt']['type'] ?? '' ? ', '.$live['/llms.txt']['type'] : '').').' : 'Not checked: website unreachable.',
                filled($llms['content'] ?? null) ? 'An llms.txt is saved in the admin. Publish the website again so it is included.' : 'Write one under AI Visibility, llms.txt. It is a proposed convention; support by AI services varies.', 'verified');
        }

        // 4. Organization and entity information
        $schema = SiteSetting::group('schema');
        $general = SiteSetting::group('general');
        foreach (['name' => 'Organization name', 'url' => 'Website URL', 'logo' => 'Logo URL', 'phone' => 'Phone', 'email' => 'Email'] as $k => $label) {
            if (blank($schema[$k] ?? null)) {
                $f[] = Finding::make("Organization data: {$label} missing", 'Entity', $k === 'logo' ? 'low' : 'medium', null, "Local SEO settings have no {$label}.", 'Fill it in under SEO, Local SEO.');
            }
        }
        foreach (['url', 'logo'] as $k) {
            if (filled($schema[$k] ?? null) && ! filter_var($schema[$k], FILTER_VALIDATE_URL)) {
                $f[] = Finding::make('Organization data: invalid URL', 'Structured data', 'medium', null, "{$k}: {$schema[$k]}", 'Use a full https:// address.');
            }
        }
        if (filled($schema['phone'] ?? null) && filled($general['phone'] ?? null) && preg_replace('/\D/', '', $schema['phone']) !== preg_replace('/\D/', '', $general['phone'])) {
            $f[] = Finding::make('Phone number differs between settings', 'Entity', 'medium', null, "Local SEO: {$schema['phone']} / Settings: {$general['phone']}", 'Use the same phone number everywhere so services can match the business.');
        }
        if (filled($schema['email'] ?? null) && filled($general['email'] ?? null) && strtolower($schema['email']) !== strtolower($general['email'])) {
            $f[] = Finding::make('Email differs between settings', 'Entity', 'low', null, "Local SEO: {$schema['email']} / Settings: {$general['email']}", 'Use one contact email consistently.');
        }
        $social = array_filter(SiteSetting::group('social'));
        if (count($social) < 2) {
            $f[] = Finding::make('Few profile links for the organization', 'Entity', 'low', null, count($social).' social profile links saved; these become "sameAs" in the Organization data.', 'Add the company LinkedIn and other official profiles under Settings.', 'recommendation');
        }
        if (blank($general['googleProfile'] ?? null)) {
            $f[] = Finding::make('No Google Business Profile link', 'Entity', 'low', null, 'Settings has no Google Business Profile link.', 'Add it if the business has a profile.', 'recommendation');
        }

        // 5. Important pages: live, indexable, in the sitemap
        $rows = collect(SeoIndex::all())->keyBy('path');
        $inSitemap = collect(Sitemap::urls())->pluck('path')->flip();
        $known = array_flip(SiteLinks::known());
        foreach (array_map([\App\Support\Permalinks::class, 'map'], self::IMPORTANT) as $p) {
            if (! isset($known[$p])) {
                $f[] = Finding::make('Important page not found', 'Indexing', 'high', $p, 'Not in the list of live pages.', 'Check the address.');
            } elseif ($rows[$p]['noindex'] ?? false) {
                $f[] = Finding::make('Important page hidden from search', 'Indexing', 'high', $p, 'Marked noindex.', 'Remove noindex.');
            } elseif (! isset($inSitemap[$p]) && isset($rows[$p])) {
                $f[] = Finding::make('Important page not in sitemap', 'Indexing', 'medium', $p, 'Missing from the admin sitemap.', 'Check the page settings.');
            }
            if ($robotsText !== '' && ! Robots::check($parsed, 'Googlebot', $p)['allowed']) {
                $f[] = Finding::make('Important page blocked by robots.txt', 'Indexing', 'critical', $p, "Blocked for Googlebot in the {$source}.", 'Remove the Disallow rule.');
            }
        }

        // 6. Answer readiness of content (heuristics, so recommendations)
        $items = Corpus::items();
        foreach ($items as $item) {
            $words = Corpus::words($item['text']);
            $questions = collect($item['headings'])->filter(fn ($h) => str_ends_with(trim((string) $h), '?'))->count();
            if (in_array($item['type'], ['Blog Post', 'News Post', 'Case Study'], true) && $words < 300) {
                $f[] = Finding::make('Short content', 'Answer readiness', 'low', $item['path'], "{$words} words of text.", 'Expand with specific facts, steps or examples so the page fully answers the topic.', 'recommendation');
            }
            if ($words >= 300 && count($item['headings']) < 2) {
                $f[] = Finding::make('Few headings', 'Answer readiness', 'low', $item['path'], count($item['headings'])." headings for {$words} words.", 'Break the text into sections with clear, descriptive headings.', 'recommendation');
            }
            if ($item['type'] === 'Blog Post' && $words >= 300 && $questions === 0) {
                $f[] = Finding::make('No question style headings', 'Answer readiness', 'info', $item['path'], 'None of the headings is phrased as a question.', 'Where it fits, use headings that match the questions people ask, followed by a direct answer.', 'recommendation');
            }
            if (filled($item['first']) && Corpus::words($item['first']) > 80) {
                $f[] = Finding::make('Long opening paragraph', 'Answer readiness', 'info', $item['path'], Corpus::words($item['first']).' words before the first heading.', 'Start with a 1 to 2 sentence direct answer or summary.', 'recommendation');
            }
        }

        // 7. Topical depth: blog categories with little content
        foreach (BlogPost::live()->get()->groupBy('category') as $cat => $posts) {
            if ($posts->count() < 3) {
                $f[] = Finding::make('Thin topic cluster', 'Topical authority', 'info', \App\Support\Permalinks::listPath('blogs'), "Category \"{$cat}\" has {$posts->count()} published ".\Illuminate\Support\Str::plural('post', $posts->count()).'.', 'Publish several in-depth posts per topic you want to be known for, and link them to the matching service page.', 'recommendation');
            }
        }

        return [
            'findings' => Finding::sort($f),
            'access' => $access,
            'robots_source' => $source,
            'reachable' => $reachable,
            'content_items' => count($items),
        ];
    }
}
