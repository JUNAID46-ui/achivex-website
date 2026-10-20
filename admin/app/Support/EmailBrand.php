<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\Content\SiteSetting;
use Illuminate\Support\Str;

// Company details for every email: logo, contact line, social links and the sender signature.
// Values come from the admin settings, with the published website details as fallback.
class EmailBrand
{
    public static function data(): array
    {
        try {
            $general = SiteSetting::group('general');
            $social = SiteSetting::group('social');
            $mail = AppSetting::group('mail');
        } catch (\Throwable) {
            [$general, $social, $mail] = [[], [], []];
        }
        $site = rtrim((string) config('admin.site_url'), '/');
        $phone = $general['phone'] ?? '+1 307 622 1140';

        return [
            'name' => $general['siteName'] ?? 'AchiveX LLC',
            'tagline' => 'Marketing & Software Solutions',
            'site' => $site,
            'siteLabel' => Str::after(Str::after($site, '://'), 'www.'),
            'logo' => $site.'/brand/logo-light.png',
            'mark' => $site.'/brand/mark-dark.png',
            'phone' => $phone,
            'phoneHref' => 'tel:'.preg_replace('/[^0-9+]/', '', $phone),
            'email' => $general['email'] ?? 'info@achivex.com',
            'location' => $general['location'] ?? 'Wyoming, USA',
            'social' => array_filter([
                'LinkedIn' => $social['linkedin'] ?? null,
                'Facebook' => $social['facebook'] ?? null,
                'X' => $social['x'] ?? null,
                'Instagram' => $social['instagram'] ?? null,
            ], fn ($url) => is_string($url) && preg_match('#^https?://#i', $url)),
            'signatureName' => $mail['signature_name'] ?? null,
            'signatureTitle' => $mail['signature_title'] ?? null,
        ];
    }
}
