<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;
use Illuminate\Support\Collection;

/**
 * Enum representing different sections in the checkout process.
 *
 * @method bool isPromo()
 * @method bool isContact()
 * @method bool isTraveller()
 * @method bool isAdditionalService()
 * @method bool isSummary()
 * @method bool isPaymentOptions()
 * @method bool isInsurance()
 * @method bool isActivity()
 * @method bool isTermsAndConditions()
 */
enum Section: string
{
    use PowerEnum;

    case Promo = 'promo';
    case Contact = 'contact';
    case Traveller = 'traveller';
    case AdditionalService = 'additional_service';
    case Summary = 'summary';
    case PaymentOptions = 'payment-options';
    case Insurance = 'insurance';
    case Activity = 'activity';
    case TermsAndConditions = 'terms-and-conditions';

    /**
     * Customize the labels of the enum values.
     *
     * @return array<string, string>
     */
    protected static function setLabels(): array
    {
        return [
            self::Promo->value => trans('checkout::page.trip_details.add_promo_code'),
            self::Contact->value => trans('checkout::page.trip_details.contact_details'),
            self::Traveller->value => trans('checkout::page.trip_details.traveller_details'),
            self::AdditionalService->value => trans('checkout::page.trip_details.additional_services'),
            self::Summary->value => trans('checkout::page.trip_details.trip_summary'),
            self::PaymentOptions->value => trans('checkout::page.trip_details.payment_options'),
            self::Insurance->value => trans('checkout::page.trip_details.Insurance'),
            self::Activity->value => trans('checkout::page.trip_details.activities'),
            self::TermsAndConditions->value => trans('checkout::page.trip_details.important_information'),
        ];
    }

    /**
     * Define the display order of the sections.
     *
     * @return Collection<int, Section>
     */
    public function displayOrder(): Collection
    {
        return collect([
            self::Contact,
            self::Traveller,
            self::Activity,
            self::Promo,
            self::AdditionalService,
            self::Insurance,
            self::TermsAndConditions,
            self::PaymentOptions,
        ]);
    }
}
