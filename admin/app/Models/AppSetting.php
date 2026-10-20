<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

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

    // Secret values are encrypted with the APP_KEY before they reach the database or the cache.
    private const SECRET_FIELDS = ['secretKey', 'password'];
    private const ENCRYPTED_PREFIX = 'enc:';

    public static function all_groups(): array
    {
        return Cache::remember('app-settings', 600, fn () => static::query()->pluck('value', 'key')->map(fn ($v) => is_string($v) ? json_decode($v, true) : $v)->all());
    }

    public static function group(string $key): array
    {
        $values = static::all_groups()[$key] ?? [];
        foreach (self::SECRET_FIELDS as $field) {
            if (is_string($values[$field] ?? null) && str_starts_with($values[$field], self::ENCRYPTED_PREFIX)) {
                try {
                    $values[$field] = Crypt::decryptString(substr($values[$field], strlen(self::ENCRYPTED_PREFIX)));
                } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                    // Saved with a different APP_KEY. Treat it as missing so it can be entered again.
                    $values[$field] = null;
                }
            }
        }

        return $values;
    }

    public static function put(string $key, array $value): void
    {
        foreach (self::SECRET_FIELDS as $field) {
            if (is_string($value[$field] ?? null) && $value[$field] !== '') {
                $value[$field] = self::ENCRYPTED_PREFIX.Crypt::encryptString($value[$field]);
            }
        }
        $row = static::firstOrNew(['key' => $key]);
        $row->value = array_merge($row->value ?? [], $value);
        $row->updated_at = now();
        $row->save();
        Cache::forget('app-settings');
    }
}
