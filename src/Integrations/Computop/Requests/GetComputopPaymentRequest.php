<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class GetComputopPaymentRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of ComputopPaymentRequest
     */
    public function __construct(public string $paymentId) {}

    /**
     * Get the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'payments/'.$this->paymentId;
    }
}
