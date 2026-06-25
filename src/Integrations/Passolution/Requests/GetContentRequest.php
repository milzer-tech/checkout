<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Requests;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Passolution\Dtos\Responses\PassolutionContentResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetContentRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  Collection<int, string>  $destinationCountryCodes
     * @param  Collection<int, string>  $nationalityCountryCodes
     */
    public function __construct(
        private readonly Collection $destinationCountryCodes,
        private readonly Collection $nationalityCountryCodes,
        private readonly string $language,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/content/all/text';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'lang' => strtolower($this->language),
            'countries' => $this->destinationCountryCodes->map(fn (string $country): string => strtolower($country))->implode(','),
            'nat' => $this->nationalityCountryCodes->map(fn (string $country): string => strtolower($country))->implode(','),
        ];
    }

    public function createDtoFromResponse(Response $response): PassolutionContentResponse
    {
        if (! $response->ok()) {
            return PassolutionContentResponse::fromPayload([]);
        }

        return PassolutionContentResponse::fromPayload($response->array());
    }
}
