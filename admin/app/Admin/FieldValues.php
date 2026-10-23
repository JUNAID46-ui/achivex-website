<?php

namespace App\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Turns submitted form values into the shapes stored in the database, and back for the form.
class FieldValues
{
    public static function fromRequest(array $field, Request $request): mixed
    {
        $name = $field['name'];
        $raw = $request->input($name);

        return match ($field['type']) {
            'toggle' => $request->boolean($name),
            'number' => $raw === null || $raw === '' ? null : (int) $raw,
            'lines' => static::lines($raw),
            'paragraphs' => static::paragraphs($raw),
            'tags' => array_values(array_filter((array) $raw)),
            'repeater', 'sections' => static::repeater($field, $raw),
            'password' => $raw === null || $raw === '' ? null : $raw,
            default => is_string($raw) ? trim($raw) : $raw,
        };
    }

    public static function lines($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw), 'strlen'));
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $raw)), 'strlen'));
    }

    public static function paragraphs($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw), 'strlen'));
        }

        return array_values(array_filter(array_map('trim', preg_split('/(\r\n|\r|\n)\s*(\r\n|\r|\n)/', (string) $raw)), 'strlen'));
    }

    public static function repeater(array $field, $raw): array
    {
        $items = is_string($raw) ? (json_decode($raw, true) ?: []) : (array) $raw;
        $sub = $field['type'] === 'sections' ? static::sectionFields() : ($field['fields'] ?? []);
        $out = [];
        foreach ($items as $item) {
            $row = [];
            foreach ($sub as $f) {
                $v = $item[$f['name']] ?? null;
                $row[$f['name']] = match ($f['type']) {
                    'lines' => static::lines($v),
                    'paragraphs' => static::paragraphs($v),
                    default => is_string($v) ? trim($v) : $v,
                };
            }
            if (collect($row)->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])->isEmpty()) {
                continue;
            }
            if ($field['type'] === 'sections') {
                $row = ['id' => Str::slug($row['heading'] ?? '') ?: 'section-'.(count($out) + 1)] + $row;
                if (empty($row['list'])) {
                    unset($row['list']);
                }
            }
            $out[] = $row;
        }

        return $out;
    }

    // Blog article sections: heading, paragraphs and an optional bullet list.
    public static function sectionFields(): array
    {
        return [
            ['name' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'paragraphs', 'label' => 'Paragraphs (blank line between paragraphs; start a line with ### for an H3 heading)', 'type' => 'paragraphs'],
            ['name' => 'list', 'label' => 'Bullet list (one item per line, optional)', 'type' => 'lines'],
        ];
    }

    // Converts a stored value into what the form input expects.
    public static function forForm(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'lines' => implode("\n", (array) $value),
            'paragraphs' => implode("\n\n", (array) $value),
            'date' => $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : ($value ? substr((string) $value, 0, 10) : ''),
            'password' => '',
            default => $value,
        };
    }
}
