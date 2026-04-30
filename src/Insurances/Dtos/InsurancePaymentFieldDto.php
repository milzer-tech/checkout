<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Dtos;

use Nezasa\Checkout\Dtos\BaseDto;

final class InsurancePaymentFieldDto extends BaseDto
{
    /**
     * Create a new instance of InsurancePaymentFieldDto.
     */
    public function __construct(
        public string $key,
        public string $type,
        public string $label,
        public ?string $placeholder = null,
        public ?string $sectionTitle = null,
        public ?string $sectionIntro = null,
        public bool $required = true,
        public ?string $requiredMessage = null,
        public ?string $invalidMessage = null,
        public string $inputMode = 'text',
        public string $autocomplete = 'off',
    ) {}

    public static function iban(): self
    {
        return new self(
            key: 'iban',
            type: 'iban',
            label: trans('checkout::page.trip_details.insurance_iban_field_label'),
            placeholder: trans('checkout::page.trip_details.insurance_iban_field_placeholder'),
            sectionTitle: trans('checkout::page.trip_details.insurance_iban_section_title'),
            sectionIntro: trans('checkout::page.trip_details.insurance_iban_section_intro'),
            requiredMessage: trans('checkout::page.trip_details.insurance_iban_validation_required'),
            invalidMessage: trans('checkout::page.trip_details.insurance_iban_validation_invalid'),
        );
    }
}
