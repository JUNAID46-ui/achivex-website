<?php

namespace App\Services\Backup;

use App\Services\SupabaseStorage;

// The private "backups" bucket in Supabase Storage. Files there are only reachable with the
// project's secret key or a short lived signed link made by an administrator.
class PrivateStorage
{
    public const BUCKET = 'backups';

    private static function url(string $suffix): string
    {
        return SupabaseStorage::baseUrl().'/storage/v1/'.$suffix;
    }

    // ['exists' => bool, 'public' => bool|null, 'error' => ?string]
    public static function status(): array
    {
        if (! SupabaseStorage::enabled()) {
            return ['exists' => false, 'public' => null, 'error' => 'The Supabase storage key is not saved under Settings.'];
        }
        try {
            $res = SupabaseStorage::client()->timeout(15)->get(static::url('bucket/'.self::BUCKET));
        } catch (\Throwable $e) {
            return ['exists' => false, 'public' => null, 'error' => 'Supabase could not be reached ('.class_basename($e).').'];
        }
        if ($res->successful()) {
            return ['exists' => true, 'public' => (bool) $res->json('public'), 'error' => null];
        }

        return ['exists' => false, 'public' => null, 'error' => in_array($res->status(), [400, 404], true) ? null : 'Supabase answered '.$res->status().'.'];
    }

    // Stored backups are only written to a bucket that exists and is private.
    public static function ready(): bool
    {
        $s = static::status();

        return $s['exists'] && $s['public'] === false;
    }

    public static function createBucket(): void
    {
        $res = SupabaseStorage::client()->timeout(15)->post(static::url('bucket'), ['id' => self::BUCKET, 'name' => self::BUCKET, 'public' => false]);
        if (! $res->successful() && ! str_contains(strtolower((string) $res->body()), 'already exists')) {
            throw new \RuntimeException('Supabase did not create the backups bucket: '.($res->json('message') ?? $res->status()));
        }
    }

    public static function put(string $path, string $contents): void
    {
        $res = SupabaseStorage::client()->timeout(120)->withHeaders(['x-upsert' => 'false'])->withBody($contents, 'application/json')
            ->post(static::url('object/'.self::BUCKET.'/'.$path));
        if (! $res->successful()) {
            throw new \RuntimeException('Supabase Storage refused the backup file: '.($res->json('message') ?? $res->status()));
        }
    }

    public static function get(string $path): string
    {
        $res = SupabaseStorage::client()->timeout(120)->get(static::url('object/'.self::BUCKET.'/'.$path));
        if (! $res->successful()) {
            throw new \RuntimeException('The backup file could not be read from storage ('.$res->status().').');
        }

        return $res->body();
    }

    public static function delete(string $path): void
    {
        $res = SupabaseStorage::client()->timeout(30)->delete(static::url('object/'.self::BUCKET), ['prefixes' => [$path]]);
        if (! $res->successful()) {
            throw new \RuntimeException('The backup file could not be deleted from storage ('.$res->status().').');
        }
    }

    // A download link valid for one minute.
    public static function signDownload(string $path, int $seconds = 60): string
    {
        $res = SupabaseStorage::client()->timeout(15)->post(static::url('object/sign/'.self::BUCKET.'/'.$path), ['expiresIn' => $seconds]);
        if (! $res->successful() || ! $res->json('signedURL')) {
            throw new \RuntimeException('Supabase did not create a download link ('.$res->status().').');
        }

        return SupabaseStorage::baseUrl().'/storage/v1'.$res->json('signedURL').'&download='.rawurlencode(basename($path));
    }

    // A one time upload link, so a large backup file goes straight from the browser to storage.
    public static function signUpload(string $path): string
    {
        $res = SupabaseStorage::client()->timeout(15)->post(static::url('object/upload/sign/'.self::BUCKET.'/'.$path));
        if (! $res->successful() || ! $res->json('url')) {
            throw new \RuntimeException('Supabase did not create an upload link ('.$res->status().').');
        }

        return SupabaseStorage::baseUrl().'/storage/v1'.$res->json('url');
    }
}
