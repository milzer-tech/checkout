<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Exceptions;

use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when another browser opens a checkout while its owner may still be paying.
 * The message deliberately does not tell that a payment is in progress.
 */
class CheckoutInUseException extends HttpException
{
    /**
     * Create a new instance of CheckoutInUseException.
     */
    public function __construct()
    {
        parent::__construct(
            statusCode: SymfonyResponse::HTTP_CONFLICT,
            message: trans('checkout::exceptions.checkout_in_use')
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
            status: SymfonyResponse::HTTP_CONFLICT
        );
    }
}
