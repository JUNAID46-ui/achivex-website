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
        // Links show the public address; images load from where the website is hosted, so they always resolve.
        $site = rtrim((string) config('admin.site_public_url'), '/');
        $assets = rtrim((string) config('admin.site_url'), '/');
        $phone = $general['phone'] ?? '+1 307 622 1140';

        return [
            'name' => $general['siteName'] ?? 'AchiveX LLC',
            'tagline' => 'Marketing & Software Solutions',
            'site' => $site,
            'siteLabel' => Str::after($site, '://'),
            'logo' => $assets.'/brand/email/logo.png',
            'mark' => $assets.'/brand/email/mark.png',
            'about' => $general['tagline'] ?? 'Empowering Businesses Through Digital Innovation.',
            'phone' => $phone,
            'phoneHref' => 'tel:'.preg_replace('/[^0-9+]/', '', $phone),
            'email' => $general['email'] ?? 'info@achivex.com',
            'location' => $general['location'] ?? 'Wyoming, USA',
            // Label => [link, round icon] for the footer and signature.
            'social' => collect(['facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'x' => 'X', 'instagram' => 'Instagram'])
                ->filter(fn ($label, $key) => is_string($social[$key] ?? null) && preg_match('#^https?://#i', $social[$key]))
                ->mapWithKeys(fn ($label, $key) => [$label => ['url' => $social[$key], 'icon' => $assets.'/brand/email/'.$key.'.png']])
                ->all(),
            'signatureName' => $mail['signature_name'] ?? null,
            'signatureTitle' => $mail['signature_title'] ?? null,
        ];
    }
}
