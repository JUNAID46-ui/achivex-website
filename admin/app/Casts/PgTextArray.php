<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

// Converts a Postgres text[] column to a PHP array and back.
class PgTextArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        if ($value === null || $value === '{}') {
            return [];
        }
        $inner = trim($value, '{}');
        $items = str_getcsv($inner, ',', '"', '\\');

        return array_values(array_filter(array_map('trim', $items), fn ($v) => $v !== ''));
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        $items = array_map(
            fn ($v) => '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $v).'"',
            array_values(array_filter((array) $value, fn ($v) => trim((string) $v) !== ''))
        );

        return '{'.implode(',', $items).'}';
    }
}
