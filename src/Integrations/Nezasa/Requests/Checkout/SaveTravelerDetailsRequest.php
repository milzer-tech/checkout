<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Nezasa\Checkout\Models\Checkout;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SaveTravelerDetailsRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     */
    public function __construct(protected readonly string $checkoutId, public Checkout $checkout) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/traveler-details";
    }

    /**
     * Define the body of the request.
     */
    protected function defaultBody(): array
    {
        return [
            'contactInfo' => $this->checkout->data['contact'],
            'paxInfo' => collect($this->checkout->data['paxInfo'])->flatten(1)->all(),
        ];
    }
}
