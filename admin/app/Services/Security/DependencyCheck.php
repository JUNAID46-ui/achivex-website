<?php

namespace App\Services\Security;

use App\Services\Audit\Finding;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// Known vulnerabilities in the PHP packages this admin runs, from composer.lock and the free
// OSV.dev database (no account or key needed). If OSV cannot be reached, it says so.
class DependencyCheck
{
    public static function packages(?string $lock = null): array
    {
        $lock ??= base_path('composer.lock');
        if (! is_readable($lock)) {
            return [];
        }
        $data = json_decode((string) file_get_contents($lock), true);

        return collect($data['packages'] ?? [])->map(fn ($p) => ['name' => $p['name'], 'version' => ltrim((string) $p['version'], 'v')])->all();
    }

    public static function findings(): array
    {
        $packages = static::packages();
        if (! $packages) {
            return [Finding::make('Dependency check not available', 'Dependencies', 'info', null, 'composer.lock could not be read on this server.', 'Run "composer audit" locally.')];
        }

        return Cache::remember('osv-scan-'.md5(json_encode($packages)), 3600, function () use ($packages) {
            try {
                $res = Http::timeout(8)->post('https://api.osv.dev/v1/querybatch', ['queries' => array_map(
                    fn ($p) => ['package' => ['ecosystem' => 'Packagist', 'name' => $p['name']], 'version' => $p['version']], $packages
                )]);
                if (! $res->ok()) {
                    throw new \RuntimeException('HTTP '.$res->status());
                }
            } catch (\Throwable $e) {
                return [Finding::make('Dependency check could not run', 'Dependencies', 'info', null, 'OSV.dev did not answer ('.class_basename($e).'). '.count($packages).' packages were not checked.', 'Try again later or run "composer audit" locally.')];
            }
            $f = [];
            foreach ($res->json('results') ?? [] as $i => $r) {
                $ids = collect($r['vulns'] ?? [])->pluck('id')->all();
                if (! $ids) {
                    continue;
                }
                $p = $packages[$i];
                $details = collect(array_slice($ids, 0, 5))->map(function ($id) {
                    $v = rescue(fn () => Http::timeout(4)->get("https://api.osv.dev/v1/vulns/{$id}")->json(), [], false);

                    return trim($id.' '.($v['summary'] ?? ''));
                });
                $f[] = Finding::make("Vulnerable package: {$p['name']}", 'Dependencies', 'high', null,
                    "{$p['name']} {$p['version']}: ".$details->implode('; ').(count($ids) > 5 ? ' (and '.(count($ids) - 5).' more)' : ''),
                    "Update {$p['name']} with composer and deploy again.");
            }

            return $f;
        });
    }
}
