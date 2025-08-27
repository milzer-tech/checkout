<?php

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Nezasa\Checkout\Exceptions\UnavailableServiceException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

it('builds UnavailableServiceException with correct status and message, and renders expected response', function (): void {
    View::prependNamespace('checkout', __DIR__.'/../../Fixtures/views');

    $expectedMessage = 'The requested service is currently unavailable. Please try again later.';
    expect(Lang::get('checkout::exceptions.unavailable_service'))->toBe($expectedMessage);

    $e = new UnavailableServiceException;

    expect($e->getStatusCode())->toBe(SymfonyResponse::HTTP_SERVICE_UNAVAILABLE)
        ->and($e->getMessage())->toBe($expectedMessage);

    $response = $e->render();

    expect($response->getStatusCode())->toBe(SymfonyResponse::HTTP_SERVICE_UNAVAILABLE)
        ->and($response->getContent())
        ->toContain('Exception View:')
        ->toContain((string) SymfonyResponse::HTTP_SERVICE_UNAVAILABLE)
        ->toContain($expectedMessage);
});
