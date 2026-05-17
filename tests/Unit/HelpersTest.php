<?php

declare(strict_types=1);

it('returns the package root path', function (): void {
    expect(checkout_path())->toBe(dirname(__DIR__, 2));
});

it('returns a package relative path', function (): void {
    expect(checkout_path('/config/checkout.php'))
        ->toBe(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'config/checkout.php');
});
