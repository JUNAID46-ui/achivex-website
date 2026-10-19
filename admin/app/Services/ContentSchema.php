<?php

namespace App\Services;

use Illuminate\Support\Str;

// Builds an editing form from a page's default content, so every fixed page
// gets an "Edit Page" screen without writing a form for each one.
class ContentSchema
{
    // Keys that link pages together or drive code. They are kept but not shown.
    private const HIDDEN = ['slug', 'key', 'id', 'category', 'caseStudy', 'single'];

    public static function fields(array $content): array
    {
        $fields = [];
        foreach ($content as $name => $value) {
            if ($field = static::field((string) $name, $value)) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private static function field(string $name, mixed $value): ?array
    {
        if (in_array($name, self::HIDDEN, true)) {
            return null;
        }
        $label = static::label($name);
        $image = static::isImageKey($name);

        if (is_array($value) && array_is_list($value)) {
            $first = $value[0] ?? null;
            if (is_array($first)) {
                $sub = [];
                foreach (array_merge(...array_map(fn ($v) => is_array($v) ? $v : [], $value)) as $k => $v) {
                    if ($f = static::field((string) $k, $v)) {
                        $sub[] = $f['type'] === 'repeater' || $f['type'] === 'group' ? ['name' => $k, 'label' => $f['label'], 'type' => 'lines'] : $f;
                    }
                }

                return ['name' => $name, 'label' => $label, 'type' => 'repeater', 'fields' => $sub];
            }
            // Lists of service slugs connect pages; editing them by hand would break links.
            if ($name === 'services' && $first !== null && preg_match('/^[a-z0-9-]+$/', (string) $first)) {
                return null;
            }

            return ['name' => $name, 'label' => $label, 'type' => $image ? 'images' : 'lines'];
        }
        if (is_array($value)) {
            return ['name' => $name, 'label' => $label, 'type' => 'group', 'fields' => static::fields($value)];
        }
        if (is_bool($value)) {
            return null;
        }
        if ($image) {
            return ['name' => $name, 'label' => $label, 'type' => 'image'];
        }
        $text = (string) $value;
        $long = mb_strlen($text) > 90 || str_contains($text, "\n") || in_array($name, ['text', 'a', 'answer', 'description', 'summary', 'intro'], true);

        return ['name' => $name, 'label' => $label, 'type' => $long ? 'textarea' : 'text'];
    }

    public static function isImageKey(string $name): bool
    {
        return (bool) preg_match('/(^|[a-z])(image|images|photo|photos|cover|logo)$/i', $name);
    }

    public static function label(string $name): string
    {
        $known = ['q' => 'Question', 'a' => 'Answer', 'faqs' => 'Questions and answers', 'heroLine' => 'Hero sub heading', 'short' => 'Short description'];
        if (isset($known[$name])) {
            return $known[$name];
        }
        $words = Str::of(Str::snake($name, ' '))->replace('_', ' ')->lower()->toString();
        $words = preg_replace(['/\bcta\b/', '/\bfaqs\b/', '/\bseo\b/'], ['call to action', 'FAQs', 'SEO'], $words);

        return Str::ucfirst($words);
    }

    // Keeps only the shape the form knows about, cleaning lists and trimming text.
    public static function clean(array $fields, array $input, array $defaults): array
    {
        $out = [];
        foreach ($fields as $f) {
            $v = $input[$f['name']] ?? null;
            $out[$f['name']] = match ($f['type']) {
                'lines', 'images' => array_values(array_filter(array_map(fn ($x) => trim((string) $x), is_array($v) ? $v : preg_split('/\r\n|\r|\n/', (string) $v)), 'strlen')),
                'repeater' => collect(is_array($v) ? $v : [])->map(fn ($row, $i) => static::clean($f['fields'], (array) $row, (array) (($defaults[$f['name']] ?? [])[$i] ?? [])) + static::hidden((array) (($defaults[$f['name']] ?? [])[$i] ?? [])))->values()->all(),
                'group' => static::clean($f['fields'], (array) $v, (array) ($defaults[$f['name']] ?? [])) + static::hidden((array) ($defaults[$f['name']] ?? [])),
                default => mb_substr(trim((string) $v), 0, 20000),
            };
        }

        return $out;
    }

    // Hidden keys (anchors, slugs) of a default item, carried over so the website keeps working.
    private static function hidden(array $default): array
    {
        return array_intersect_key($default, array_flip(self::HIDDEN));
    }
}
