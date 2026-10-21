<?php

namespace App\Services\Audit;

// Minimal robots.txt reader: which group applies to a crawler and whether a path is allowed
// (longest matching rule wins, Allow wins a tie), following RFC 9309.
class Robots
{
    public static function parse(string $content): array
    {
        $groups = [];
        $current = null;
        $lastWasAgent = false;
        $sitemaps = [];
        foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line));
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field = strtolower($field);
            if ($field === 'user-agent') {
                if (! $lastWasAgent) {
                    $groups[] = ['agents' => [], 'rules' => []];
                    $current = count($groups) - 1;
                }
                $groups[$current]['agents'][] = strtolower($value);
                $lastWasAgent = true;
            } elseif (in_array($field, ['allow', 'disallow'], true) && $current !== null) {
                $groups[$current]['rules'][] = ['type' => $field, 'path' => $value];
                $lastWasAgent = false;
            } elseif ($field === 'sitemap') {
                $sitemaps[] = $value;
            } else {
                $lastWasAgent = false;
            }
        }

        return ['groups' => $groups, 'sitemaps' => $sitemaps];
    }

    // ['allowed' => bool, 'group' => '*' | agent | null, 'rule' => 'Disallow: /' | null]
    public static function check(array $parsed, string $agent, string $path = '/'): array
    {
        $agent = strtolower($agent);
        $match = null;
        foreach ($parsed['groups'] as $g) {
            if (in_array($agent, $g['agents'], true)) {
                $match = $g;
                $name = $agent;
            }
        }
        if (! $match) {
            foreach ($parsed['groups'] as $g) {
                if (in_array('*', $g['agents'], true)) {
                    $match = $g;
                    $name = '*';
                }
            }
        }
        if (! $match) {
            return ['allowed' => true, 'group' => null, 'rule' => null];
        }
        $best = null;
        foreach ($match['rules'] as $r) {
            if ($r['path'] === '') {
                continue; // "Disallow:" with no path allows everything
            }
            $pattern = '#^'.str_replace(['\*', '\$'], ['.*', '$'], preg_quote($r['path'], '#')).'#';
            if (preg_match($pattern, $path)) {
                $len = strlen($r['path']);
                if (! $best || $len > $best['len'] || ($len === $best['len'] && $r['type'] === 'allow')) {
                    $best = $r + ['len' => $len];
                }
            }
        }

        return [
            'allowed' => ! $best || $best['type'] === 'allow',
            'group' => $name,
            'rule' => $best ? ucfirst($best['type']).': '.$best['path'] : null,
        ];
    }
}
