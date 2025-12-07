<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Vertical\Resources;

use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\PurchaseEventPayload;
use Nezasa\Checkout\Integrations\Vertical\Requests\PurchaseEventRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class VerticalPurchaseResource extends BaseResource
{
    /**
     * Send a request to register the quote.
     */
    public function eventHostCancellation(PurchaseEventPayload $payload): Response
    {
        return $this->connector->send(
            new PurchaseEventRequest($payload)
        );
    }
}
