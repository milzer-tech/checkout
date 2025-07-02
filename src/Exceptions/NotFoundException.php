<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Exceptions;

use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
    /**
     * Create a new instance of the ParentException
     */
    public function __construct()
    {
        parent::__construct(
            message: 'The requested resource could not be retrieved from Nezasa API.',
            code: SymfonyResponse::HTTP_NOT_FOUND,
        );
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): LaravelResponse
    {
        return new LaravelResponse(
            content: view(view: 'checkout::exceptions.all')->with('exception', $this),
            status: SymfonyResponse::HTTP_NOT_FOUND,
        );
    }
}
