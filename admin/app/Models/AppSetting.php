<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

// Private admin settings (email server, storage key) kept in the admin schema,
// so they can be changed from the Settings screen instead of the .env file.
class AppSetting extends Model
{
    protected $table = 'app_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['value' => 'array', 'updated_at' => 'datetime'];

    public static function all_groups(): array
    {
        return Cache::remember('app-settings', 600, fn () => static::query()->pluck('value', 'key')->map(fn ($v) => is_string($v) ? json_decode($v, true) : $v)->all());
    }

    public static function group(string $key): array
    {
        return static::all_groups()[$key] ?? [];
    }

    public static function put(string $key, array $value): void
    {
        $row = static::firstOrNew(['key' => $key]);
        $row->value = array_merge($row->value ?? [], $value);
        $row->updated_at = now();
        $row->save();
        Cache::forget('app-settings');
    }
}
