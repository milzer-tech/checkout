<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Exceptions;

use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnavailableServiceException extends HttpException
{
    /**
     * Create a new instance of the ParentException
     */
    public function __construct()
    {
        parent::__construct(
            statusCode: SymfonyResponse::HTTP_SERVICE_UNAVAILABLE,
            message: 'The requested service is currently unavailable. Please try again later.');
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): LaravelResponse
    {
        return new LaravelResponse(
            content: view(view: 'checkout::exceptions.all')->with('exception', $this),
            status: SymfonyResponse::HTTP_SERVICE_UNAVAILABLE
        );
    }
}
