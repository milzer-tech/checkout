<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Foundation\Vite as LaravelVite;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class NoViteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Replace the Vite service with a no-op object that mimics the API.
        $this->app->singleton(LaravelVite::class, fn (): object => new class
        {
            /** Render tags for entry points: vite('resources/js/app.js', ...) */
            public function __invoke(...$entryPoints): HtmlString
            {
                return new HtmlString('');
            }

            /** Some layouts call Vite::reactRefresh() */
            public function reactRefresh(): HtmlString
            {
                return new HtmlString('');
            }

            public function asset(string $path, bool $build = true): string
            {
                return $path;
            }

            public function hotAsset(string $path): string
            {
                return $path;
            }

            public function isRunningHot(): bool
            {
                return false;
            }

            /** Fluent config methods — just return $this so chains don’t break */
            public function useHotFile($path = null): self
            {
                return $this;
            }

            public function useBuildDirectory($dir = 'build'): self
            {
                return $this;
            }

            public function useManifest($path): self
            {
                return $this;
            }

            public function useScriptTagAttributes($value): self
            {
                return $this;
            }

            public function useStyleTagAttributes($value): self
            {
                return $this;
            }

            public function cspNonce(?string $nonce): self
            {
                return $this;
            }

            public function withEntryPoints(...$entryPoints): self
            {
                return $this;
            }

            public function toHtml(): string
            {
                return '';
            }

            public function __toString(): string
            {
                return '';
            }

            public function __call(string $name, array $arguments)
            {
                $renderish = ['render', 'tags', 'scripts', 'styles'];
                if (in_array($name, $renderish, true)) {
                    return new HtmlString('');
                }

                return $this;
            }
        });
    }

    public function boot(): void
    {
        Blade::directive('vite', fn (): string => '');
    }
}
