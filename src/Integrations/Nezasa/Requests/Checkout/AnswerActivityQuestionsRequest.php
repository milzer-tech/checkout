<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\AnswerActivityQuestionPayloadDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\ActivityQuestionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Http\Faking\FakeResponse;
use Saloon\Http\PendingRequest;
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

    public function middleware(): MiddlewarePipeline
    {
        if (! Config::boolean('checkout.fake_calls')) {
            return parent::middleware();
        }

        return parent::middleware()
            ->onRequest(function (PendingRequest $pendingRequest) {
                $file = file_get_contents(
                    checkout_path('tests/Fixtures/Saloon/get_activity_question_response.json')
                );

                if ($file === false) {
                    return new FakeResponse('fake file not found: get_activity_question_response.json');
                }

                $data = json_decode($file, true);

                /** @phpstan-ignore-next-line */
                return new FakeResponse($data['data'], $data['statusCode'], $data['headers']);
            });
    }
}
