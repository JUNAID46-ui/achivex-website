<?php

namespace App\Support;

// CSV cells that start with = + - @ or a tab/return run as formulas in Excel and Sheets.
// Contact messages and page views come from the public website, so every exported cell is
// prefixed with a quote when it starts with one of those characters.
class Csv
{
    public static function cell($value): string
    {
        $value = (string) $value;

        return $value !== '' && str_contains("=+-@\t\r", $value[0]) ? "'".$value : $value;
    }

    public static function row(array $values): array
    {
        return array_map([static::class, 'cell'], $values);
    }
}
