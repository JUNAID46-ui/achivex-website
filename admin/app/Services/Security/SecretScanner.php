<?php

namespace App\Services\Security;

use App\Services\Audit\Finding;
use Symfony\Component\Finder\Finder;

// Looks for credentials written into the deployed code and config files. Matches are masked:
// the full value is never stored or shown. .env files are not scanned (they are meant to hold secrets
// and are not deployed); vendor, node_modules and storage are skipped.
class SecretScanner
{
    public const DIRS = ['app', 'config', 'routes', 'resources/views', 'database', 'bootstrap', 'api', 'public/js'];

    public const PATTERNS = [
        'Private key' => '/-----BEGIN (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----/',
        'AWS access key' => '/\b(AKIA|ASIA)[0-9A-Z]{16}\b/',
        'Supabase secret key' => '/\bsb_secret_[A-Za-z0-9_\-]{20,}/',
        'Supabase service role JWT' => '/\beyJ[A-Za-z0-9_\-]{10,}\.[A-Za-z0-9_\-]*c2VydmljZV9yb2xl[A-Za-z0-9_\-]*\.[A-Za-z0-9_\-]{10,}/',
        'Stripe secret key' => '/\b(sk|rk)_live_[A-Za-z0-9]{20,}/',
        'GitHub token' => '/\b(ghp|gho|ghu|ghs|github_pat)_[A-Za-z0-9_]{30,}/',
        'Slack token' => '/\bxox[abpr]-[A-Za-z0-9\-]{20,}/',
        'Google API key' => '/\bAIza[0-9A-Za-z_\-]{35}\b/',
        'Laravel APP_KEY value' => '/base64:[A-Za-z0-9+\/]{43}=/',
        'Database URL with password' => '/\b(postgres(?:ql)?|mysql):\/\/[^:\s\/]+:[^@\s\/]{6,}@/',
    ];

    public static function scan(?string $root = null): array
    {
        $root ??= base_path();
        $hits = [];
        $dirs = array_values(array_filter(array_map(fn ($d) => $root.'/'.$d, self::DIRS), 'is_dir'));
        if (! $dirs) {
            return [];
        }
        $finder = (new Finder)->files()->in($dirs)->ignoreDotFiles(false)->notName(['.env', '.env.*'])->size('< 2M')
            ->exclude(['vendor', 'node_modules', 'storage', 'cache']);
        foreach ($finder as $file) {
            $lines = @file($file->getRealPath()) ?: [];
            foreach ($lines as $i => $line) {
                foreach (self::PATTERNS as $name => $re) {
                    if (preg_match($re, $line, $m)) {
                        $hits[] = ['file' => ltrim(str_replace($root, '', $file->getRealPath()), '/'), 'line' => $i + 1, 'type' => $name, 'masked' => static::mask($m[0])];
                    }
                }
            }
        }

        return $hits;
    }

    public static function mask(string $value): string
    {
        $len = strlen($value);

        return $len <= 8 ? str_repeat('*', $len) : substr($value, 0, 4).str_repeat('*', min(12, $len - 6)).substr($value, -2);
    }

    public static function findings(): array
    {
        return array_map(fn ($h) => Finding::make("Possible {$h['type']} in code", 'Secrets', 'critical', null,
            "{$h['file']} line {$h['line']}: {$h['masked']}",
            'Move the value to an environment variable, rotate the credential (it may already be in Git history), and remove it from the file.'), static::scan());
    }
}
