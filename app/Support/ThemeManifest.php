<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ThemeManifest
{
    private const LEGACY_MAP = [
        'light' => 'light-pro',
        'dark' => 'dark-pro',
        'soft-blue' => 'pixel-ui',
        'soft-green' => 'one-ui',
        'soft-purple' => 'pixel-ui',
        'apple-glass-light' => 'apple-glass',
        'apple-glass-dark' => 'apple-glass',
        'one-ui-light' => 'one-ui',
        'one-ui-dark' => 'one-ui',
        'nothing-os' => 'nothing-os',
        'pixel-ui' => 'pixel-ui',
    ];

    public static function all(): array
    {
        $base = public_path('themes');
        if (!File::isDirectory($base)) {
            return [];
        }

        $items = [];
        foreach (File::directories($base) as $dir) {
            $slug = basename($dir);
            $metaPath = $dir . DIRECTORY_SEPARATOR . 'meta.json';
            if (!File::exists($metaPath)) {
                continue;
            }

            $meta = json_decode((string) File::get($metaPath), true);
            if (!is_array($meta) || empty($meta['slug'])) {
                continue;
            }

            $items[$slug] = [
                'name' => (string) ($meta['name'] ?? $slug),
                'slug' => (string) ($meta['slug'] ?? $slug),
                'author' => (string) ($meta['author'] ?? 'internal'),
                'dark' => (bool) ($meta['dark'] ?? false),
                'version' => (string) ($meta['version'] ?? '1.0'),
                'description' => (string) ($meta['description'] ?? ''),
                'theme_color' => (string) ($meta['theme_color'] ?? ($meta['dark'] ?? false ? '#0f1115' : '#f8fafc')),
                'preview' => is_array($meta['preview'] ?? null) ? $meta['preview'] : [],
                'assets' => [
                    'css' => asset('themes/' . $slug . '/theme.css'),
                    'js' => asset('themes/' . $slug . '/theme.js'),
                    'preview' => asset('themes/' . $slug . '/preview.png'),
                ],
            ];
        }

        ksort($items);
        return $items;
    }

    public static function slugs(): array
    {
        return array_keys(static::all());
    }

    public static function defaultSlug(): string
    {
        $themes = static::all();
        return array_key_exists('dark-pro', $themes) ? 'dark-pro' : (array_key_first($themes) ?: 'dark-pro');
    }

    public static function normalizeSlug(?string $slug): string
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return static::defaultSlug();
        }

        $slug = static::LEGACY_MAP[$slug] ?? $slug;
        return array_key_exists($slug, static::all()) ? $slug : static::defaultSlug();
    }
}
