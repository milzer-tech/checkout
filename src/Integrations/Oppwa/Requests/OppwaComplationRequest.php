<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Requests;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaComplationPayload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasFormBody;
use Throwable;

class OppwaComplationRequest extends Request implements HasBody
{
    use HasFormBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of OppwaComplationRequest
     *
     * @see https://axcessms.docs.oppwa.com/integrations/backoffice#
     */
    public function __construct(
        public readonly string $id,
        public readonly OppwaComplationPayload $payload
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'v1/payments/'.$this->id;
    }

    /**
     * Define the body of the request.
     *
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return $this->payload->toArray();
    }

    /**
     * Check if the request has failed based on the response.
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        $successfulResultCode = Config::string('checkout.integrations.oppwa.successful_result_code');

        try {
            return $response->array('result.code') !== $successfulResultCode;
        } catch (Throwable $exception) {
            report($exception);

            return true;
        }
    }
}
