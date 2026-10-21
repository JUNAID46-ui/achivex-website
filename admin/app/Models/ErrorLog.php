<?php

namespace App\Models;

use App\Services\Security\SecretScanner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Application errors, kept in the database so the System Health page can show them
// (Vercel logs are not readable from the app). Messages are shortened and scrubbed.
class ErrorLog extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];

    private static bool $writing = false;

    public static function capture(\Throwable $e): void
    {
        if (static::$writing) {
            return; // an error while saving an error must not loop
        }
        static::$writing = true;
        try {
            static::create([
                'level' => 'error',
                'exception' => mb_substr(get_class($e), 0, 191),
                'message' => static::scrub($e->getMessage()),
                'location' => mb_substr(Str::after($e->getFile(), base_path().DIRECTORY_SEPARATOR).':'.$e->getLine(), 0, 300),
                'method' => app()->runningInConsole() ? 'CLI' : request()->method(),
                'path' => app()->runningInConsole() ? null : mb_substr('/'.ltrim(request()->path(), '/'), 0, 300),
                'user_id' => auth()->id(),
            ]);
        } catch (\Throwable) {
            // Database unavailable: the error still goes to the normal log.
        } finally {
            static::$writing = false;
        }
    }

    // Drops SQL text (it can hold submitted values), connection strings and anything that looks like a credential.
    public static function scrub(string $message): string
    {
        $message = preg_replace('/\(Connection: .*$/s', '(SQL details removed)', $message);
        $message = preg_replace('/\bSQL: .*$/s', 'SQL details removed', $message);
        foreach (SecretScanner::PATTERNS as $re) {
            $message = preg_replace($re, '[redacted]', $message);
        }
        $message = preg_replace('/(password|secret|token|key)(["\']?\s*[:=]\s*)\S+/i', '$1$2[redacted]', $message);

        return mb_substr($message, 0, 500);
    }
}
