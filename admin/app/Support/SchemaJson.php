<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

// Custom JSON-LD schema typed into the SEO forms. Accepts one object or a list of objects,
// each with an "@type", and returns the decoded value (null when the box is empty).
class SchemaJson
{
    public static function parse(?string $raw, string $field = 'schema'): ?array
    {
        if (trim((string) $raw) === '') {
            return null;
        }
        $schema = json_decode($raw, true);
        $items = is_array($schema) && array_is_list($schema) ? $schema : [$schema];
        $valid = json_last_error() === JSON_ERROR_NONE && is_array($schema) && $items
            && collect($items)->every(fn ($s) => is_array($s) && ! empty($s['@type']));
        if (! $valid) {
            throw ValidationException::withMessages([
                $field => 'The schema is not valid JSON-LD. It must be a JSON object (or a list of objects) and each one needs an "@type".',
            ]);
        }

        return $schema;
    }
}
