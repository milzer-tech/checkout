<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Throwable;

class AnswerActivityQuestionsRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::POST;

    /**
     * Create a new instance of AnswerActivityQuestionsRequest
     *
     * @param  Collection<int, AnswerActivityQuestionPayloadDto>  $payload
     */
    public function __construct(
        public readonly string $checkoutId,
        public Collection $payload,
    ) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return "/checkout/v1/checkouts/$this->checkoutId/activity-questions";
    }

    /**
     * Define the body of the request.
     *
     * @return array<int, array<string, string>>
     */
    protected function defaultBody(): array
    {
        return $this->payload->toArray();
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
