<?php

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Nezasa\Checkout\Exceptions\AlreadyPaidException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

it('builds AlreadyPaidException with correct status and message, and renders expected response', function (): void {
    View::prependNamespace('checkout', __DIR__.'/../../Fixtures/views');

    $expectedMessage = 'This itinerary is already paid.';
    expect(Lang::get('checkout::exceptions.already_paid'))->toBe($expectedMessage);

    $e = new AlreadyPaidException;

    expect($e->getStatusCode())->toBe(SymfonyResponse::HTTP_CONFLICT)
        ->and($e->getMessage())->toBe($expectedMessage);

    $response = $e->render();

    expect($response->getStatusCode())->toBe(SymfonyResponse::HTTP_NOT_FOUND)
        ->and($response->getContent())
        ->toContain('Exception View:')
        ->toContain((string) SymfonyResponse::HTTP_CONFLICT)
        ->toContain($expectedMessage);
});
