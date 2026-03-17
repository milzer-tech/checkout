<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Resources;

use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurPaymentPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Requests\HanseMerkurPaymentRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class HanseMerkurPaymentResource extends BaseResource
{
    /**
     * Pay the quote.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function pay(HanseMerkurPaymentPayload $payload): Response
    {
        return $this->connector->send(
            new HanseMerkurPaymentRequest($payload)
        );
    }
}
