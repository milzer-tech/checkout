<?php

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
