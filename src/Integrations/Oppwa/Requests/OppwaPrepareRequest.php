<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Requests;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Payloads\OppwaPreparePayload;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\OppwaPrepareResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasFormBody;

class OppwaPrepareRequest extends Request implements HasBody
{
    use HasFormBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of ApplyPromoCodeRequest
     */
    public function __construct(public OppwaPreparePayload $payload) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'v1/checkouts';
    }

    protected function defaultBody(): array
    {
        //        if(! $this->payload->entityId){
        //            $this->payload->entityId = Config::string('checkout.payment.oppwa.entity_id');
        //        }
        //        $data = $this->payload->toArray();
        //        $data['customer.email'] = 'azim@milzer.de';
        //        $data['customer.givenName'] = 'John';
        //        $data['customer.surname'] = 'Doe';
        //        $data['billing.street1'] = '123 Main St';
        //        $data['billing.city'] = 'Anytown';
        //        $data['billing.postcode'] = '12345';
        //        $data['billing.country'] = 'US';

        return $this->payload->toArray();
    }

    /**
     * Cast the response to a DTO.
     */
    public function createDtoFromResponse(Response $response): OppwaPrepareResponse
    {
        return OppwaPrepareResponse::from($response->array());
    }
}
