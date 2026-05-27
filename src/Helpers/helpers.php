<?php

declare(strict_types=1);

if (! function_exists('checkout_path')) {
    /**
     * Get the root path of the package.
     */
    function checkout_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);

        $path = ltrim($path, '/\\');

        return $path === ''
            ? $root
            : $root.DIRECTORY_SEPARATOR.$path;
    }
}

if (! function_exists('checkout_asset_data_uri')) {
    /**
     * Convert a package asset into a browser-ready data URI.
     */
    function checkout_asset_data_uri(string $path, string $mimeType): ?string
    {
        $assetPath = checkout_path($path);

        if (! is_file($assetPath) || ! is_readable($assetPath)) {
            return null;
        }

        $contents = file_get_contents($assetPath);

        return $contents === false
            ? null
            : 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}
