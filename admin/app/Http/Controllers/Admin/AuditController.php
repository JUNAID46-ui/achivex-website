<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AuditRun;
use App\Models\Content\SiteSetting;
use App\Services\Audit\AiVisibilityAudit;
use App\Services\Audit\AuditRunner;
use App\Services\Audit\Finding;
use App\Services\SeoIndex;
use Illuminate\Http\Request;

// SEO audit and AI visibility pages: run audits, read findings, history and downloads, llms.txt.
class AuditController extends Controller
{
    private function page(Request $request, string $type, string $view, array $extra = [])
    {
        $runs = AuditRun::where('type', $type)->latest('started_at')->latest('id')->limit(20)->get(['id', 'status', 'summary', 'started_at', 'finished_at', 'user_id', 'error']);
        $run = $request->input('run') ? AuditRun::where('type', $type)->findOrFail((int) $request->input('run')) : AuditRun::where('type', $type)->where('status', 'completed')->latest('started_at')->latest('id')->first();
        $findings = collect($run?->findings ?? []);
        if ($s = $request->input('severity')) {
            $findings = $findings->where('severity', $s);
        }
        if (in_array($k = $request->input('kind'), ['verified', 'recommendation'], true)) {
            $findings = $findings->where('kind', $k);
        }
        if ($c = $request->input('category')) {
            $findings = $findings->where('category', $c);
        }
        if ($q = mb_strtolower(trim((string) $request->input('q')))) {
            $findings = $findings->filter(fn ($f) => str_contains(mb_strtolower($f['check'].' '.$f['url'].' '.$f['evidence']), $q));
        }
        $previous = $run ? AuditRun::where('type', $type)->where('status', 'completed')->where('id', '<', $run->id)->latest('id')->first(['id', 'summary']) : null;

        return view($view, [
            'type' => $type, 'runs' => $runs, 'run' => $run, 'previous' => $previous,
            'findings' => $findings->values(), 'categories' => collect($run?->findings ?? [])->pluck('category')->unique()->sort()->values(),
        ] + $extra);
    }

    public function seo(Request $request)
    {
        return $this->page($request, 'seo', 'admin.seo.audit');
    }

    public function ai(Request $request)
    {
        return $this->page($request, 'ai', 'admin.seo.ai-visibility', [
            'llms' => SiteSetting::group('llms'),
            'crawlers' => AiVisibilityAudit::CRAWLERS,
        ]);
    }

    public function run(Request $request, string $type)
    {
        abort_unless(in_array($type, ['seo', 'ai'], true), 404);
        $run = AuditRunner::run($type);
        $route = AuditRunner::TYPES[$type]['route'];

        return redirect()->route($route, ['run' => $run->id])->with('status', $run->status === 'completed'
            ? "Audit finished with {$run->summary['total']} findings."
            : 'The audit stopped with an error. Nothing was changed on the website.');
    }

    public function download(Request $request, AuditRun $run, string $format)
    {
        abort_unless(in_array($run->type, ['seo', 'ai'], true) && $run->status === 'completed', 404);
        abort_unless(in_array($format, ['csv', 'json'], true), 404);
        ActivityLog::record('exported', 'Audit report', "Run #{$run->id}", strtoupper($format), ['module' => 'Audits', 'id' => $run->id]);
        $name = "achivex-{$run->type}-audit-{$run->started_at?->format('Y-m-d-Hi')}";
        if ($format === 'json') {
            return response()->json([
                'type' => $run->type, 'run' => $run->id, 'started_at' => $run->started_at?->toIso8601String(),
                'note' => 'Verified findings were observed in the admin data or the live website. Recommendations are advice, not measurements.',
                'summary' => $run->summary, 'findings' => $run->findings,
            ], 200, ['Content-Disposition' => "attachment; filename={$name}.json"]);
        }

        return response()->streamDownload(AuditRunner::csv($run), "{$name}.csv", ['Content-Type' => 'text/csv']);
    }

    public function saveLlms(Request $request)
    {
        $data = $request->validate(['content' => 'nullable|string|max:20000']);
        $content = str_replace("\r\n", "\n", (string) ($data['content'] ?? ''));
        SiteSetting::put('llms', ['content' => $content, 'updated_at' => now()->toIso8601String()]);
        ActivityLog::record('updated', 'SEO', 'llms.txt', strlen($content).' characters', ['module' => 'AI Visibility']);

        return redirect()->to(route('admin.seo.ai').'#llms')->with('status', 'llms.txt saved. Publish the website again to put it live.');
    }

    // A starting point built from the saved site settings and live pages. Nothing is saved until the user saves it.
    public function draftLlms()
    {
        $general = SiteSetting::group('general');
        $schema = SiteSetting::group('schema');
        $base = rtrim((string) config('admin.site_public_url'), '/');
        $rows = collect(SeoIndex::all())->reject(fn ($r) => $r['noindex']);
        $section = fn (string $title, $items) => $items->isEmpty() ? '' : "\n## {$title}\n\n".$items->map(fn ($r) => "- [{$r['title']}]({$base}{$r['path']})".(filled($r['description']) ? ': '.str_replace("\n", ' ', $r['description']) : ''))->implode("\n")."\n";

        $text = '# '.($schema['name'] ?? $general['siteName'] ?? 'AchiveX LLC')."\n\n"
            .'> '.($general['tagline'] ?? 'Marketing and software solutions.')."\n\n"
            .collect([
                filled($general['location'] ?? null) ? 'Location: '.$general['location'] : null,
                filled($general['phone'] ?? null) ? 'Phone: '.$general['phone'] : null,
                filled($general['email'] ?? null) ? 'Email: '.$general['email'] : null,
            ])->filter()->implode("\n")."\n"
            .$section('Main pages', $rows->where('type', 'Page')->take(15))
            .$section('Services', $rows->whereIn('type', ['Service area', 'Service'])->take(40))
            .$section('Articles', $rows->where('type', 'Blog post')->take(30))
            .$section('Case studies', $rows->where('type', 'Case study')->take(20));

        return response()->json(['content' => $text]);
    }
}
