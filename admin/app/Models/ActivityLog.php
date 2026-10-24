<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Append only record of admin actions. Rows are never edited or deleted: the app has no route
// for it and a database trigger blocks it.
class ActivityLog extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime', 'context' => 'array'];

    // Actions shown in the Security Center as security sensitive.
    public const SENSITIVE = ['login', 'login_failed', 'logout_all', 'role_changed', 'password_changed', 'two_factor', 'deleted', 'restored', 'published', 'settings', 'exported', 'ip_blocked', 'ip_unblocked'];

    // Keys that are never stored in the context column, whatever the caller passes.
    private const SECRET_KEYS = '/pass|token|secret|api_?key|private_?key|cookie|authorization|otp|recovery/i';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Records an admin action. $options: module, id (record key), status (success|failure), context (array).
    public static function record(string $action, ?string $type = null, ?string $label = null, ?string $description = null, array $options = []): void
    {
        $user = auth()->user();
        static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? ($options['user_name'] ?? null),
            'user_role' => $user?->role,
            'action' => $action,
            'subject_type' => $type,
            'subject_label' => $label ? mb_substr($label, 0, 250) : null,
            'subject_id' => isset($options['id']) ? mb_substr((string) $options['id'], 0, 191) : null,
            'module' => $options['module'] ?? $type,
            'status' => $options['status'] ?? 'success',
            'description' => $description ? mb_substr($description, 0, 250) : null,
            'context' => isset($options['context']) ? static::safeContext($options['context']) : null,
            'ip' => request()->ip(),
        ]);
    }

    public static function failure(string $action, ?string $type, ?string $label, ?string $description = null, array $options = []): void
    {
        static::record($action, $type, $label, $description, ['status' => 'failure'] + $options);
    }

    // Drops secret looking keys and trims long values, so a context can never hold a password or token.
    public static function safeContext(array $context): array
    {
        $out = [];
        foreach ($context as $k => $v) {
            if (preg_match(self::SECRET_KEYS, (string) $k)) {
                continue;
            }
            $out[$k] = is_array($v) ? static::safeContext($v) : (is_scalar($v) || $v === null ? (is_string($v) ? Str::limit($v, 200) : $v) : null);
        }

        return $out;
    }

    // Activity rows are immutable.
    public function save(array $options = [])
    {
        abort_if($this->exists, 403, 'Activity log entries cannot be changed.');

        return parent::save($options);
    }

    public function delete()
    {
        abort(403, 'Activity log entries cannot be deleted.');
    }
}
