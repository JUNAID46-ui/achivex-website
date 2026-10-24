<?php

namespace App\Support;

// Design options (config/design.php) for fixed website pages: keeps only known keys and safe
// values, and drops empty ones so the website falls back to its own styling.
class DesignOptions
{
    public static function field(): array
    {
        return config('design');
    }

    public static function clean(mixed $input): array
    {
        $input = (array) $input;
        $out = [];
        foreach (config('design.fields') as $f) {
            $v = trim((string) ($input[$f['name']] ?? ''));
            $v = match ($f['type']) {
                'color' => preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) ? $v : '',
                'select' => array_key_exists($v, $f['options']) ? $v : '',
                'image' => preg_match('#^https://\S+$#', $v) ? mb_substr($v, 0, 500) : '',
                default => mb_substr($v, 0, 200),
            };
            if ($v !== '') {
                $out[$f['name']] = $v;
            }
        }

        return $out;
    }
}
