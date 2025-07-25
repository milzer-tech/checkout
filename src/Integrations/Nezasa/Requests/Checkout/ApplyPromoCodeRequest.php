<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ApplyPromoCodeRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of ApplyPromoCodeRequest
     */
    public function __construct(
        public readonly string $checkoutId,
        public readonly string $code,
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/promo-codes";
    }

    /**
     * Define the body of the request
     *
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return [
            'promoCode' => $this->code,
        ];
    }
}
