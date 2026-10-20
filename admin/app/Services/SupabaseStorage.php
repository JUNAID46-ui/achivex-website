<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;

// Uploads files to the public "media" bucket in Supabase Storage, so images chosen in the
// admin load on the live website. Needs the project's secret key (Settings, Image storage).
class SupabaseStorage
{
    public const BUCKET = 'media';

    public static function baseUrl(): string
    {
        return rtrim((string) (AppSetting::group('storage')['url'] ?? '') ?: config('services.supabase.url'), '/');
    }

    public static function key(): ?string
    {
        return (AppSetting::group('storage')['secretKey'] ?? null) ?: config('services.supabase.secret_key');
    }

    public static function enabled(): bool
    {
        return (bool) static::key();
    }

    public static function publicUrl(string $path): string
    {
        return static::baseUrl().'/storage/v1/object/public/'.static::BUCKET.'/'.$path;
    }

    private static function client(?string $key = null)
    {
        $key ??= static::key();
        // New "sb_secret_" keys go in the apikey header only; older JWT keys also go in Authorization.
        $headers = ['apikey' => $key];
        if (str_starts_with((string) $key, 'eyJ')) {
            $headers['Authorization'] = 'Bearer '.$key;
        }

        return Http::withHeaders($headers)->timeout(60);
    }

    public static function put(string $path, string $contents, string $mime): string
    {
        $res = static::client()->withHeaders(['x-upsert' => 'true', 'cache-control' => 'max-age=31536000'])
            ->withBody($contents, $mime)
            ->post(static::baseUrl().'/storage/v1/object/'.static::BUCKET.'/'.$path);
        if (! $res->successful()) {
            throw new \RuntimeException('Supabase Storage refused the upload: '.($res->json('message') ?? $res->body()));
        }

        return static::publicUrl($path);
    }

    public static function delete(string $path): void
    {
        static::client()->delete(static::baseUrl().'/storage/v1/object/'.static::BUCKET, ['prefixes' => [$path]]);
    }

    // A one time upload link, so the browser sends the file straight to Supabase.
    // Big files never pass through Vercel, which only accepts requests up to 4.5 MB.
    public static function signUpload(string $path): string
    {
        $res = static::client()->post(static::baseUrl().'/storage/v1/object/upload/sign/'.static::BUCKET.'/'.$path);
        if (! $res->successful() || ! $res->json('url')) {
            throw new \RuntimeException('Supabase did not create an upload link: '.($res->json('message') ?? $res->status()));
        }

        return static::baseUrl().'/storage/v1'.$res->json('url');
    }

    // Size and type of an uploaded file, as Supabase stores it. Null when the file is not there.
    public static function head(string $path): ?array
    {
        $res = \Illuminate\Support\Facades\Http::timeout(30)->head(static::publicUrl($path));
        if (! $res->successful()) {
            return null;
        }

        return ['size' => (int) $res->header('Content-Length'), 'mime' => strtok((string) $res->header('Content-Type'), ';')];
    }

    // Used by the Settings screen to check a key before saving it.
    public static function check(string $key): ?string
    {
        try {
            $res = static::client($key)->timeout(15)->get(static::baseUrl().'/storage/v1/bucket/'.static::BUCKET);
        } catch (\Throwable $e) {
            return 'Could not reach Supabase: '.$e->getMessage();
        }

        return $res->successful() ? null : 'Supabase did not accept this key ('.$res->status().'). Copy the secret key again from Supabase.';
    }
}
