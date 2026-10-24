<?php

namespace App\Services\Audit;

// One audit finding. "verified" means it was observed in the database, a saved setting or a live
// response; "recommendation" is advice based on good practice or a heuristic, not a measured fact.
class Finding
{
    public const SEVERITIES = ['critical' => 5, 'high' => 4, 'medium' => 3, 'low' => 2, 'info' => 1];

    public static function make(string $check, string $category, string $severity, ?string $url, string $evidence, string $fix, string $kind = 'verified'): array
    {
        return [
            'check' => $check,
            'category' => $category,
            'severity' => isset(self::SEVERITIES[$severity]) ? $severity : 'info',
            'url' => $url,
            'evidence' => mb_substr($evidence, 0, 500),
            'fix' => $fix,
            'kind' => $kind === 'recommendation' ? 'recommendation' : 'verified',
        ];
    }

    public static function sort(array $findings): array
    {
        usort($findings, fn ($a, $b) => [self::SEVERITIES[$b['severity']], $a['category'], (string) $a['url']] <=> [self::SEVERITIES[$a['severity']], $b['category'], (string) $b['url']]);

        return $findings;
    }

    public static function summary(array $findings): array
    {
        $c = collect($findings);

        return [
            'total' => $c->count(),
            'by_severity' => collect(array_keys(self::SEVERITIES))->mapWithKeys(fn ($s) => [$s => $c->where('severity', $s)->count()])->all(),
            'verified' => $c->where('kind', 'verified')->count(),
            'recommendations' => $c->where('kind', 'recommendation')->count(),
            'by_category' => $c->groupBy('category')->map->count()->all(),
        ];
    }
}
