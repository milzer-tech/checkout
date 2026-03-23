<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Exceptions;

use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotStoredTravellerException extends HttpException
{
    /**
     * Create a new instance of the ParentException
     *
     * @param  array<int|string, string|array<string, mixed>>  $travellers
     */
    public function __construct(array $travellers = [])
    {
        parent::__construct(
            statusCode: SymfonyResponse::HTTP_CONFLICT,
            message: 'Traveller not stored: '.json_encode($travellers),
        );
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): LaravelResponse
    {
        return new LaravelResponse(
            /** @phpstan-ignore-next-line */
            content: view(view: 'checkout::exceptions.all')->with('exception', $this),
            status: SymfonyResponse::HTTP_NOT_FOUND
        );
    }
}
