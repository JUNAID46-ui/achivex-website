<?php

namespace App\Support;

use App\Models\Content\SiteSetting;

// Website colours and fonts (Settings, Colors and Fonts). Mirrors website/src/lib/theme.js.
class Theme
{
    // key => [label, help, default]
    public const COLORS = [
        'Dark sections' => [
            'darkBg' => ['Background', 'Most of the site, the header and the footer.', '#0B0D12'],
            'darkHeading' => ['Headings', 'Headings and strong text on dark backgrounds.', '#FFFFFF'],
            'darkText' => ['Body text', 'Paragraphs on dark backgrounds.', '#C9CED6'],
            'accent' => ['Accent', 'Buttons, numbers and icons on dark backgrounds.', '#B6F500'],
            'onAccent' => ['Text on accent', 'Button text on the accent colour.', '#0B0D12'],
        ],
        'Light sections' => [
            'lightBg' => ['Background', 'White sections.', '#FFFFFF'],
            'lightHeading' => ['Headings', 'Headings on light backgrounds.', '#0B0D12'],
            'lightText' => ['Body text', 'Paragraphs on light backgrounds.', '#0B0D12'],
            'lightAccent' => ['Accent', 'Buttons and links on light backgrounds.', '#0F2C9C'],
            'lightOnAccent' => ['Text on accent', 'Button text on the light accent.', '#FFFFFF'],
            'lightCards' => ['Cards', 'Cards and form fields inside light sections.', '#C9CED6'],
        ],
        'Other' => [
            'brandBlue' => ['Brand blue sections', 'Blue bands such as call to action blocks.', '#0F2C9C'],
            'panel' => ['Light panels', 'Side panels in menus.', '#EEF1F3'],
            'heroCard' => ['Hero dark card', 'The black card in the home page hero.', '#000000'],
        ],
    ];

    // Must match FONTS in website/src/lib/theme.js.
    public const FONTS = ['Manrope', 'Geist', 'Inter', 'DM Sans', 'Plus Jakarta Sans', 'Montserrat', 'Poppins', 'Raleway', 'Work Sans', 'Outfit',
        'Sora', 'Space Grotesk', 'IBM Plex Sans', 'Source Sans 3', 'Open Sans', 'Roboto', 'Lato', 'Playfair Display', 'Lora', 'Merriweather'];

    public const DEFAULT_FONTS = ['body' => 'Manrope', 'heading' => 'Geist'];

    // Pairs whose contrast is checked: [text, background, label].
    public const PAIRS = [
        ['darkText', 'darkBg', 'Body text on dark background'],
        ['darkHeading', 'darkBg', 'Headings on dark background'],
        ['onAccent', 'accent', 'Button text on accent'],
        ['lightText', 'lightBg', 'Body text on light background'],
        ['lightOnAccent', 'lightAccent', 'Button text on light accent'],
        ['darkHeading', 'brandBlue', 'Headings on brand blue'],
    ];

    public static function defaults(): array
    {
        return collect(self::COLORS)->flatMap(fn ($group) => collect($group)->map(fn ($c) => $c[2]))->all();
    }

    public static function current(): array
    {
        $saved = SiteSetting::group('theme');
        $colors = static::defaults();
        foreach ($saved['colors'] ?? [] as $k => $v) {
            if (isset($colors[$k]) && is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
                $colors[$k] = strtoupper($v);
            }
        }
        $fonts = self::DEFAULT_FONTS;
        foreach (['body', 'heading'] as $k) {
            if (in_array($saved['fonts'][$k] ?? null, self::FONTS, true)) {
                $fonts[$k] = $saved['fonts'][$k];
            }
        }

        return ['colors' => $colors, 'fonts' => $fonts];
    }

    // WCAG 2 contrast ratio between two #RRGGBB colours.
    public static function contrast(string $a, string $b): float
    {
        $lum = function (string $hex) {
            $c = array_map(fn ($i) => hexdec(substr($hex, $i, 2)) / 255, [1, 3, 5]);
            $c = array_map(fn ($v) => $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $c);

            return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
        };
        [$l1, $l2] = [$lum($a), $lum($b)];

        return round((max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05), 2);
    }

    // Pairs below 4.5:1, the WCAG AA level for normal text.
    public static function warnings(array $colors): array
    {
        $out = [];
        foreach (self::PAIRS as [$fg, $bg, $label]) {
            $ratio = static::contrast($colors[$fg], $colors[$bg]);
            if ($ratio < 4.5) {
                $out[] = "{$label}: contrast {$ratio}:1, below 4.5:1, so it may be hard to read.";
            }
        }

        return $out;
    }
}
