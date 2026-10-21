<?php

namespace App\Services;

use Illuminate\Support\Str;

// On-page SEO checks for one URL. Returns a 0 to 100 score and a list of checks.
class SeoAnalyzer
{
    public static function analyze(array $in): array
    {
        $title = trim((string) ($in['title'] ?? ''));
        $desc = trim((string) ($in['description'] ?? ''));
        $keyword = Str::lower(trim((string) ($in['keyword'] ?? '')));
        $path = (string) ($in['path'] ?? '');
        $text = Str::lower(strip_tags((string) ($in['text'] ?? '')));
        $first = Str::lower((string) ($in['first'] ?? ''));
        $headings = array_map(fn ($h) => Str::lower((string) $h), (array) ($in['headings'] ?? []));
        $words = str_word_count($text);
        $checks = [];

        // $group is a short, stable name used to count problems across pages.
        $add = function (string $status, string $label, string $tip = '', string $group = '') use (&$checks) {
            $checks[] = ['status' => $status, 'label' => $label, 'tip' => $tip, 'group' => $group ?: $label];
        };
        $kw = function (bool $ok, string $where, string $tip = '', string $miss = 'bad') use ($add) {
            $add($ok ? 'good' : $miss, $ok ? "Focus keyword appears in {$where}." : "Focus keyword is missing from {$where}.", $ok ? '' : $tip, "Keyword missing from {$where}");
        };

        // Title
        $len = mb_strlen($title);
        if ($len === 0) {
            $add('bad', 'The SEO title is missing.', 'Write a title of 30 to 60 characters.', 'SEO title missing');
        } elseif ($len < 30) {
            $add('ok', "The SEO title is short ({$len} characters).", 'Aim for 30 to 60 characters so it uses the space Google shows.', 'SEO title too short');
        } elseif ($len > 60) {
            $add('ok', "The SEO title is long ({$len} characters).", 'Google may cut it off after about 60 characters.', 'SEO title too long');
        } else {
            $add('good', "The SEO title length is good ({$len} characters).");
        }

        // Description
        $dlen = mb_strlen($desc);
        if ($dlen === 0) {
            $add('bad', 'The meta description is missing.', 'Write 120 to 160 characters that describe the page.', 'Meta description missing');
        } elseif ($dlen < 120) {
            $add('ok', "The meta description is short ({$dlen} characters).", 'Aim for 120 to 160 characters.', 'Meta description too short');
        } elseif ($dlen > 160) {
            $add('ok', "The meta description is long ({$dlen} characters).", 'Google may cut it off after about 160 characters.', 'Meta description too long');
        } else {
            $add('good', "The meta description length is good ({$dlen} characters).");
        }

        // Focus keyword
        if ($keyword === '') {
            $add('bad', 'No focus keyword is set.', 'Choose the main phrase people would search for to find this page.', 'No focus keyword');
        } else {
            $kw(Str::contains(Str::lower($title), $keyword), 'the SEO title', 'Use the keyword near the start of the title.');
            $kw(Str::contains(Str::lower($desc), $keyword), 'the meta description', 'Mention the keyword once in the description.');
            $kw(Str::contains($path, Str::slug($keyword)), 'the URL', 'Short URLs that contain the keyword help a little.', 'ok');
            if ($first !== '') {
                $kw(Str::contains($first, $keyword), 'the first paragraph', 'Mention the keyword early in the text.', 'ok');
            }
            if ($headings) {
                $kw(collect($headings)->contains(fn ($h) => Str::contains($h, $keyword)), 'the subheadings', 'Use the keyword or a close variation in at least one heading.', 'ok');
            }
            if ($words > 0) {
                $hits = substr_count($text, $keyword);
                $density = round($hits * max(1, str_word_count($keyword)) * 100 / $words, 1);
                $status = $density >= 0.5 && $density <= 3 ? 'good' : ($hits > 0 ? 'ok' : 'bad');
                $add($status, "Keyword density is {$density}% ({$hits} times).", 'Aim for 0.5% to 3%. Write naturally rather than repeating the phrase.', $density > 3 ? 'Keyword used too often' : 'Keyword used too rarely');
            }
        }

        // Content
        if ($words > 0) {
            $status = $words >= 300 ? 'good' : ($words >= 150 ? 'ok' : 'bad');
            $add($status, "The content has {$words} words.", 'Pages with 300 or more words usually rank better.', 'Thin content (under 300 words)');
            $sentences = max(1, preg_match_all('/[.!?]+(\s|$)/', $text));
            $avg = round($words / $sentences);
            $add($avg <= 20 ? 'good' : 'ok', "Average sentence length is {$avg} words.", 'Shorter sentences are easier to read.', 'Long sentences');
        }

        if (! empty($in['noindex'])) {
            $add('ok', 'This page is hidden from search engines (noindex).', 'Turn off noindex if you want it to appear in Google.', 'Hidden from search engines');
        }

        $points = collect($checks)->sum(fn ($c) => ['good' => 1, 'ok' => 0.5, 'bad' => 0][$c['status']]);
        $score = $checks ? (int) round($points / count($checks) * 100) : 0;

        return ['score' => $score, 'checks' => $checks];
    }

    public static function grade(int $score): string
    {
        return $score >= 80 ? 'good' : ($score >= 50 ? 'ok' : 'bad');
    }

    public static function color(int $score): string
    {
        return ['good' => 'border border-green-600 !text-green-700', 'ok' => 'border border-ink !text-ink', 'bad' => 'border border-red-600 !text-red-600'][static::grade($score)];
    }
}
