<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

// Authenticator app codes (TOTP, RFC 6238: 6 digits, 30 seconds, SHA1) and recovery codes.
class TwoFactor
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function newSecret(): string
    {
        $bytes = random_bytes(20);
        $bits = '';
        foreach (str_split($bytes) as $b) {
            $bits .= str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($chunk) => self::ALPHABET[bindec($chunk)], str_split($bits, 5)));
    }

    public static function code(string $secret, int $step): string
    {
        $bits = '';
        foreach (str_split(strtoupper($secret)) as $c) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $c)), 5, '0', STR_PAD_LEFT);
        }
        $key = implode('', array_map(fn ($byte) => chr(bindec($byte)), str_split(substr($bits, 0, intdiv(strlen($bits), 8) * 8), 8)));
        $hash = hash_hmac('sha1', pack('J', $step), $key, true);
        $offset = ord($hash[19]) & 0xF;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    // Accepts the current code or one 30 seconds either side, and never the same code twice.
    public static function verify(string $secret, string $code, int|string $userKey): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) {
            return false;
        }
        $now = intdiv(time(), 30);
        foreach ([0, -1, 1] as $drift) {
            $step = $now + $drift;
            if (hash_equals(self::code($secret, $step), $code)) {
                return Cache::add("2fa-used:{$userKey}:{$step}", 1, 120);
            }
        }

        return false;
    }

    public static function recoveryCodes(): array
    {
        return collect(range(1, 8))->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))->all();
    }

    // Uses up a recovery code if it matches one the user still has.
    public static function useRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $code = Str::lower(trim($code));
        foreach ($codes as $i => $saved) {
            if (hash_equals($saved, $code)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    public static function qrSvg(string $secret, string $email): string
    {
        $uri = 'otpauth://totp/'.rawurlencode('AchiveX Admin:'.$email).'?'.http_build_query(['secret' => $secret, 'issuer' => 'AchiveX Admin']);

        return (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString($uri);
    }
}
