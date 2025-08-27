<?php

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

it('builds NotFoundException with correct status and message, and renders expected response', function (): void {
    View::prependNamespace('checkout', __DIR__.'/../../Fixtures/views');

    $expectedMessage = 'The requested resource could not be retrieved from Nezasa API.';
    expect(Lang::get('checkout::exceptions.not_found_resource'))->toBe($expectedMessage);

    $e = new NotFoundException;

    expect($e->getStatusCode())->toBe(SymfonyResponse::HTTP_NOT_FOUND)
        ->and($e->getMessage())->toBe($expectedMessage);

    $response = $e->render();

    expect($response->getStatusCode())->toBe(SymfonyResponse::HTTP_NOT_FOUND)
        ->and($response->getContent())
        ->toContain('Exception View:')
        ->toContain((string) SymfonyResponse::HTTP_NOT_FOUND)
        ->toContain($expectedMessage);
});
