<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Models\AuditRun;
use App\Services\Notifier;

// Runs an audit, stores the result in the history and tells the people who can act on it.
class AuditRunner
{
    public const TYPES = [
        'seo' => ['label' => 'SEO audit', 'area' => 'seo', 'route' => 'admin.seo.audit'],
        'ai' => ['label' => 'AI visibility audit', 'area' => 'seo', 'route' => 'admin.seo.ai'],
        'security' => ['label' => 'Security check', 'area' => 'security', 'route' => 'admin.security'],
    ];

    public static function run(string $type): AuditRun
    {
        $run = AuditRun::create(['type' => $type, 'status' => 'running', 'user_id' => auth()->id(), 'started_at' => now()]);
        $meta = self::TYPES[$type];
        try {
            $result = match ($type) {
                'seo' => ['findings' => SeoAudit::run()],
                'ai' => AiVisibilityAudit::run(),
                'security' => \App\Services\Security\SecurityScan::run(),
            };
            $findings = $result['findings'];
            $summary = Finding::summary($findings) + collect($result)->except('findings')->all();
            $run->update(['status' => 'completed', 'findings' => $findings, 'summary' => $summary, 'finished_at' => now()]);
            ActivityLog::record('audit', $meta['label'], "Run #{$run->id}", "{$summary['total']} findings", ['module' => 'Audits', 'id' => $run->id]);

            $serious = ($summary['by_severity']['critical'] ?? 0) + ($summary['by_severity']['high'] ?? 0);
            Notifier::send("{$type}_audit", $serious ? ($summary['by_severity']['critical'] ? 'critical' : 'warning') : 'info',
                "{$meta['label']} finished: {$summary['total']} findings".($serious ? ", {$serious} high or critical" : ''),
                null, $meta['label'], route($meta['route'], ['run' => $run->id]), $meta['area']);
        } catch (\Throwable $e) {
            report($e);
            $run->update(['status' => 'failed', 'error' => class_basename($e).': '.mb_substr($e->getMessage(), 0, 300), 'finished_at' => now()]);
            ActivityLog::failure('audit', $meta['label'], "Run #{$run->id}", 'The audit stopped with an error', ['module' => 'Audits', 'id' => $run->id]);
            Notifier::send('task_failed', 'warning', "{$meta['label']} failed", 'The audit stopped with an error. Details are in the audit history.', $meta['label'], route($meta['route']), $meta['area']);
        }

        return $run->fresh();
    }

    // CSV of a run's findings, with formula-safe cells.
    public static function csv(AuditRun $run): \Closure
    {
        return function () use ($run) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Severity', 'Kind', 'Category', 'Check', 'URL', 'Evidence', 'Recommended fix', 'Audit date (UTC)']);
            foreach ($run->findings ?? [] as $f) {
                fputcsv($out, array_map([\App\Support\Csv::class, 'cell'], [$f['severity'], $f['kind'] === 'verified' ? 'Verified' : 'Recommendation', $f['category'], $f['check'], $f['url'], $f['evidence'], $f['fix'], $run->started_at?->format('Y-m-d H:i')]));
            }
            fclose($out);
        };
    }
}
