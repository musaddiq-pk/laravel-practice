<?php

if (!function_exists('vite')) {
    function vite(string $asset): string
    {
        $manifestPath = public_path('build/manifest.json');

        if (!file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        $entry = $manifest[$asset]['file'] ?? null;

        return $entry
            ? asset("build/{$entry}")
            : '';
    }
}
