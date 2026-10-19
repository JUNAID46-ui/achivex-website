<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'public.site_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['value' => 'array', 'updated_at' => 'datetime'];

    // Groups already read in this request, so pages that ask several times hit the database once.
    private static array $loaded = [];

    // Read one settings group as an array.
    public static function group(string $key): array
    {
        return static::$loaded[$key] ??= static::find($key)?->value ?? [];
    }

    // Save one settings group, merging with what is already stored.
    public static function put(string $key, array $value): void
    {
        $row = static::firstOrNew(['key' => $key]);
        $row->value = array_merge($row->value ?? [], $value);
        $row->updated_at = now();
        $row->save();
        static::$loaded[$key] = $row->value;
    }
}
