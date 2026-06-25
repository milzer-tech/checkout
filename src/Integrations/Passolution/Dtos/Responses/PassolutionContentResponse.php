<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class PassolutionContentResponse extends BaseDto
{
    /**
     * @param  Collection<int, PassolutionRecordResponse>  $records
     */
    public function __construct(
        public Collection $records = new Collection,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $records = $payload['records'] ?? [];

        return new self(
            records: collect(is_array($records) ? $records : [])
                ->filter(fn (mixed $record): bool => is_array($record))
                ->map(fn (array $record): PassolutionRecordResponse => PassolutionRecordResponse::fromPayload($record))
                ->values()
        );
    }

    public function recordForCombination(string $destinationCountryCode, string $nationalityCountryCode): ?PassolutionRecordResponse
    {
        return $this->records->first(
            fn (PassolutionRecordResponse $record): bool => $record->destination === $destinationCountryCode
                && $record->nationality === $nationalityCountryCode
        );
    }
}
