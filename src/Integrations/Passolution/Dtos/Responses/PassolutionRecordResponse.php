<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Passolution\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;

class PassolutionRecordResponse extends BaseDto
{
    public function __construct(
        public ?string $destination = null,
        public ?string $nationality = null,
        public ?string $title = null,
        public ?PassolutionSectionResponse $entry = null,
        public ?PassolutionSectionResponse $visa = null,
        public ?PassolutionSectionResponse $transitVisa = null,
        public ?PassolutionSectionResponse $health = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            destination: $payload['destination'] ?? null,
            nationality: $payload['nationality'] ?? data_get($payload, 'traveller.nationality'),
            title: $payload['title'] ?? null,
            entry: self::section($payload['entry'] ?? null),
            visa: self::section($payload['visa'] ?? null),
            transitVisa: self::section($payload['transit_visa'] ?? null),
            health: self::section($payload['health'] ?? null),
        );
    }

    public function healthContent(): ?string
    {
        return $this->health?->content;
    }

    public function entryContent(): ?string
    {
        return $this->entry?->content;
    }

    public function visaContent(): ?string
    {
        return $this->visa?->content;
    }

    public function transitVisaContent(): ?string
    {
        return $this->transitVisa?->content;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private static function section(?array $payload): ?PassolutionSectionResponse
    {
        if ($payload === null) {
            return null;
        }

        return new PassolutionSectionResponse(
            language: $payload['language'] ?? null,
            title: $payload['title'] ?? null,
            content: $payload['content'] ?? null,
            updatedAt: $payload['updated_at'] ?? null,
        );
    }
}
