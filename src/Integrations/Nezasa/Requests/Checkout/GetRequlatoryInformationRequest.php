<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class GetRequlatoryInformationRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     */
    public function __construct(protected readonly string $checkoutId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/regulatory-information";
    }

    /**
     * Cast the response to a DTO.
     *
     *
     * @return Collection<int, ActivityQuestionResponse>
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        throw_unless(condition: $response->ok(), exception: NotFoundException::class);

        return collect(
            ActivityQuestionResponse::collect($response->array())
        );
    }
}
