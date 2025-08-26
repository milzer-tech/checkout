<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\AddressEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class PaxInfoPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of ContactInfoPayloadEntity.
     */
    public function __construct(
        public string $refId,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?int $age = null,
        public ?GenderEnum $gender = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public ?Carbon $birthDate = null,
        public ?string $nationality = null,
        public ?string $nationalityCountryCode = null,
        public ?string $passportNumber = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public ?Carbon $passportExpirationDate = null,
        public ?string $secondOrAdditionalName = null,
        public ?string $passportIssuingCountry = null,
        public ?string $passportIssuingCountryCode = null,
        public ?bool $isMainContact = null,
        public ?bool $externalRefId = null,
        public ?AddressEntity $address = null,
    ) {}

    protected static function getManipulatedAttributes(): array
    {
        return [
            'birthDate',
            'passportExpirationDate',
        ];
    }

    /**
     * Create a new instance of the DTO from the given payloads.
     *
     * @phpstan-ignore-next-line
     */
    public static function from(...$payloads): static
    {
        $payloads[0]['address'] = AddressEntity::from($payloads[0]);

        return parent::from(...$payloads);
    }
}
