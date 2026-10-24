<?php

namespace App\Support;

use App\Models\Content\SiteSetting;

// Website colours and fonts (Settings, Colors and Fonts). Mirrors website/src/lib/theme.js.
class Theme
{
    // key => [label, help, default]. Must match PALETTE in website/src/lib/theme.js.
    public const COLORS = [
        'Dark sections' => [
            'heroBg' => ['Page heroes and navy bands', 'The top of every inner page, Why AchiveX, the dark cards and banners.', '#07152D'],
            'darkBg' => ['Dark sections', 'The home intro and numbers, and every "How we work" timeline.', '#1C1C1C'],
            'footerBg' => ['Call to action and footer', 'The closing call to action band and the footer.', '#000000'],
            'mutedText' => ['Text on dark backgrounds', 'Paragraphs and labels on navy, dark and black sections. Headings there stay white.', '#A3A3A3'],
        ],
        'Light sections' => [
            'greyBg' => ['Grey sections', 'The light grey bands between white sections.', '#EEF0F3'],
            'heading' => ['Headings', 'Headings and strong text on white and grey sections.', '#1C1C1C'],
            'text' => ['Body text', 'Paragraphs on white and grey sections.', '#4B515B'],
            'cards' => ['Cards', 'Card and summary box backgrounds on white sections.', '#F6F7F9'],
            'lines' => ['Lines and borders', 'Card borders, dividers and the grey part of progress lines.', '#E3E6EB'],
        ],
        'Brand' => [
            'brand' => ['Brand blue', 'Links, small labels above headings, buttons, numbers and the active menu item.', '#0F2C9C'],
        ],
    ];

    // Must match FONTS in website/src/lib/theme.js.
    public const FONTS = ['Manrope', 'Geist', 'Inter', 'DM Sans', 'Plus Jakarta Sans', 'Montserrat', 'Poppins', 'Raleway', 'Work Sans', 'Outfit',
        'Sora', 'Space Grotesk', 'IBM Plex Sans', 'Source Sans 3', 'Open Sans', 'Roboto', 'Lato', 'Playfair Display', 'Lora', 'Merriweather'];

    public const DEFAULT_FONTS = ['body' => 'Manrope', 'heading' => 'Geist'];

    // Pairs whose contrast is checked: [text, background, label].
    public const PAIRS = [
        ['mutedText', 'heroBg', 'Text on page heroes'],
        ['mutedText', 'darkBg', 'Text on dark sections'],
        ['mutedText', 'footerBg', 'Text on the footer'],
        ['heading', 'greyBg', 'Headings on grey sections'],
        ['text', 'greyBg', 'Body text on grey sections'],
        ['text', 'cards', 'Body text on cards'],
        ['brand', 'greyBg', 'Brand blue on grey sections'],
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
