<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\AddressEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;

class ContactInfoPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of ContactInfoPayloadEntity.
     */
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $companyName = null,
        public ?GenderEnum $gender = null,
        public ?string $email = null,
        public ?string $mobilePhone = null,
        public ?string $taxNumber = null,
        public ?string $localIdNumber = null,
        public ?AddressEntity $address = null,
    ) {}

    /**
     * Create a new instance of the DTO from the given payloads.
     */
    public static function from(mixed ...$payloads): static
    {
        $payloads[0]['address'] = AddressEntity::from($payloads[0]);

        return parent::from(...$payloads);
    }
}
