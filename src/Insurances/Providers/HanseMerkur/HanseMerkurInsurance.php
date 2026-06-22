<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Providers\HanseMerkur;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDocumentLinkDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceTerms;
use Nezasa\Checkout\Integrations\HanseMerkur\Connectors\HanseMerkurConnector;
use Nezasa\Checkout\Integrations\HanseMerkur\Connectors\HanseMerkurPaymentConnector;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurAddressPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurAllocationPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurContactDataPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurCoveredEventPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurCustomerPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurInsuredPersonPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurMoneyEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurPaymentMethodPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\Entities\HanseMerkurProductPayloadEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurCreateOffersPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurPaymentPayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Payloads\HanseMerkurReservePayload;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities\HanseMerkurDocumentResponseEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Dtos\Responses\Entities\HanseMerkurOfferProductResponseEntity;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurDocumentTypeEnum;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurGenderEnum;
use Nezasa\Checkout\Integrations\HanseMerkur\Enums\HanseMerkurPaymentTypeEnum;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\AddressEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\TravelerRequirementFieldEnum;
use Nezasa\Checkout\Models\Transaction;

final class HanseMerkurInsurance implements InsuranceContract
{
    public static function isActive(): bool
    {
        return Config::boolean('checkout.insurance.hanse_merkur.active');
    }

    public static function getName(): string
    {
        return Config::string('checkout.insurance.hanse_merkur.name');
    }

    public static function getLogo(): ?string
    {
        $configuredLogo = Config::get('checkout.insurance.hanse_merkur.logo');

        return is_string($configuredLogo) && $configuredLogo !== ''
            ? $configuredLogo
            : checkout_asset_data_uri('src/Resources/assets/images/hanse-merkur-logo.png', 'image/png');
    }

    public function getPaymentFields(): array
    {
        return [];
    }

    public function getContactRequirements(): array
    {
        return [
            //            'firstName' => TravelerRequirementFieldEnum::Required,
            //            'lastName' => TravelerRequirementFieldEnum::Required,
            'email' => TravelerRequirementFieldEnum::Required,
            //            'street1' => TravelerRequirementFieldEnum::Required,
            //            'postalCode' => TravelerRequirementFieldEnum::Required,
            //            'country' => TravelerRequirementFieldEnum::Required,
            //            'gender' => TravelerRequirementFieldEnum::Required,
        ];
    }

    public function getPassengerRequirements(): array
    {
        return [
            'firstName' => TravelerRequirementFieldEnum::Required,
            'lastName' => TravelerRequirementFieldEnum::Required,
            'gender' => TravelerRequirementFieldEnum::Required,
            'birthDate' => TravelerRequirementFieldEnum::Required,
            'street1' => TravelerRequirementFieldEnum::Required,
            'postalCode' => TravelerRequirementFieldEnum::Required,
            'city' => TravelerRequirementFieldEnum::Required,
            'country' => TravelerRequirementFieldEnum::Required,
        ];
    }

    public function getNoSelectionText(): string
    {
        return trans('checkout::page.trip_details.insurance_no_insurance_option');
    }

    public function shouldAddOfferPriceToPayment(): bool
    {
        return true;
    }

    public function getSeparatePaymentNotice(InsuranceOfferDto $selectedOffer): ?string
    {
        return null;
    }

    public function makeNezasaPaymentTransactionPayload(
        Transaction $transaction,
        InsuranceOfferDto $selectedOffer,
        InsuranceBookOfferResult $result
    ): ?CreatePaymentTransactionPayload {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOffers(CreateInsuranceOffersDto $createOffersDto): InsuranceOffersResult
    {
        $response = HanseMerkurConnector::make()->offers()->create(
            payload: new HanseMerkurCreateOffersPayload(
                coveredEvent: $this->createCoveredEvent($createOffersDto),
                insuredPersons: $this->getInsuredPersons($createOffersDto->paxInfo),
            )
        );

        if (! $response->successful()) {
            return new InsuranceOffersResult(
                isSuccessful: false,
                meta: $response->array(),
                /** @phpstan-ignore-next-line */
                errorMessage: collect(data_get($response->array(), 'messages', []))->pluck('message')->implode(',')
            );
        }

        $offers = [];

        /** @var HanseMerkurOfferProductResponseEntity $product */
        foreach ($response->dto()->offers->pluck('products')->flatten()->sortBy('productTotalPremium.amount') as $product) {
            $terms = [];
            foreach ($product->documents as $document) {
                if ($document->documentType?->mustBeDisplayed() && is_string($document->url) && $document->url !== '') {
                    $text = $this->documentLabel($document->documentType);
                    $terms[] = '<a target="_blank" href="'.$document->url.'">'.$text.'</a>';
                }
            }
            $terms[] = '<a target="_blank" href="https://www.hmrv.de/datenschutz">HanseMerkur Reiseversicherung AG</a>';

            $offers[] = new InsuranceOfferDto(
                id: $product->productInstanceId,
                title: $product->title ?? 'Unknown',
                price: Price::from($product->productTotalPremium),
                coverage: $product->coverageData->pluck('title')->toArray(),
                terms: new InsuranceTerms(
                    checkboxText: 'Ich habe das Informationsblatt zu den Versicherungsprodukten zur Kenntnis genommen und akzeptiere die Allgemeinen Versicherungsbedingungen sowie die Übertragung der für die Buchung notwendigen Daten an die HanseMerkur Reiseversicherung',
                    conditions: $terms
                ),
                documentLinks: $this->documentLinksForProduct($product),
            );
        }

        return new InsuranceOffersResult(isSuccessful: $response->ok(), offers: $offers, meta: $response->array());
    }

    public function bookOffer(BookInsuranceOfferDto $bookOfferDto): InsuranceBookOfferResult
    {
        $insuredPersons = $this->getInsuredPersons($bookOfferDto->createdOfferDto->paxInfo);

        $payload = new HanseMerkurReservePayload(
            coveredEvent: $this->createCoveredEvent($bookOfferDto->createdOfferDto),
            insuredPersons: $insuredPersons,
            insuranceCustomer: $this->createInsuranceCustomer($bookOfferDto->createdOfferDto),
            products: collect([
                new HanseMerkurProductPayloadEntity(
                    productInstanceId: $bookOfferDto->selectedOffer->id,
                    insuredPersonAllocations: $insuredPersons->map(
                        fn (HanseMerkurInsuredPersonPayloadEntity $person): HanseMerkurAllocationPayloadEntity => new HanseMerkurAllocationPayloadEntity(
                            insuredPersonId: $person->insuredPersonId,
                        )
                    )
                ),
            ]),
        );

        $reserveResponse = HanseMerkurConnector::make()->offers()->reserve(payload: $payload);

        if (! $reserveResponse->successful()) {
            return new InsuranceBookOfferResult(isSuccessful: false, data: $reserveResponse->array());
        }

        $payResponse = HanseMerkurPaymentConnector::make()->payment()->pay(
            payload: new HanseMerkurPaymentPayload(
                policyNumber: $reserveResponse->dto()->policyDetail->policyNumber,
                paymentMethod: new HanseMerkurPaymentMethodPayloadEntity(
                    type: HanseMerkurPaymentTypeEnum::AgencyEncashment
                )
            )
        );

        return new InsuranceBookOfferResult(
            isSuccessful: $payResponse->successful(),
            confirmationId: $reserveResponse->dto()->policyDetail->policyNumber,
            data: [
                'reserve_response' => $reserveResponse->array(),
                'payment_response' => $payResponse->array()]
        );
    }

    public function getNezasaPayload(AddCustomInsurancePayload $payload): AddCustomInsurancePayload
    {
        return $payload;
    }

    private function createCoveredEvent(CreateInsuranceOffersDto $createOffersDto): HanseMerkurCoveredEventPayloadEntity
    {
        return new HanseMerkurCoveredEventPayloadEntity(
            bookingConfirmationDate: now()->toImmutable(),
            eventStartDate: $createOffersDto->startDate,
            eventEndDate: $createOffersDto->endDate,
            totalEventCost: HanseMerkurMoneyEntity::from($createOffersDto->totalPrice),
            destinationCountries: $createOffersDto->destinationCountries->toArray(),
        );
    }

    /**
     * Create the insured persons' payload.
     *
     * @param  Collection<int, PaxInfoPayloadEntity>  $paxInfo
     * @return Collection<int, HanseMerkurInsuredPersonPayloadEntity>
     */
    private function getInsuredPersons(Collection $paxInfo): Collection
    {
        $persons = [];

        foreach ($paxInfo->values() as $key => $pax) {
            $persons[] = new HanseMerkurInsuredPersonPayloadEntity(
                insuredPersonId: $key + 1,
                birthDate: $pax->birthDate->toImmutable(),
                givenName: $pax->firstName,
                surname: $pax->lastName,
                gender: $pax->gender->isFemale() ? HanseMerkurGenderEnum::Female : HanseMerkurGenderEnum::Male,
            );
        }

        return collect($persons);
    }

    private function createInsuranceCustomer(CreateInsuranceOffersDto $createOffersDto): HanseMerkurCustomerPayloadEntity
    {
        /** @var PaxInfoPayloadEntity $firstTraveller */
        $firstTraveller = $createOffersDto->paxInfo->values()->first();
        /** @var AddressEntity $address */
        $address = $firstTraveller->address;

        return new HanseMerkurCustomerPayloadEntity(
            contactData: new HanseMerkurContactDataPayloadEntity(
                email: $createOffersDto->contact->email,
                address: $this->createAddress($address),
                telephone: $createOffersDto->contact->mobilePhone,
            ),
            countryOfResidence: $this->countryCode($address),
            gender: $firstTraveller->gender->isFemale() ? HanseMerkurGenderEnum::Female : HanseMerkurGenderEnum::Male,
            birthDate: $firstTraveller->birthDate->toImmutable(),
            givenName: $firstTraveller->firstName,
            surname: $firstTraveller->lastName,
        );
    }

    private function createAddress(AddressEntity $address): HanseMerkurAddressPayloadEntity
    {
        return new HanseMerkurAddressPayloadEntity(
            countryIsoCode: $this->countryCode($address),
            postalCode: $address->postalCode,
            cityName: $address->city,
            streetName: $address->street1,
            streetNumber: $address->street2 ?? 'Unknown',
        );
    }

    private function countryCode(AddressEntity $address): string
    {
        return $address->getCountryCode() ?: $address->countryCode ?: 'DE';
    }

    /**
     * @return array<int, InsuranceOfferDocumentLinkDto>
     */
    private function documentLinksForProduct(HanseMerkurOfferProductResponseEntity $product): array
    {
        return $product->documents
            ->filter(fn (HanseMerkurDocumentResponseEntity $document): bool => $document->documentType?->mustBeDisplayed() === true && is_string($document->url) && $document->url !== '')
            ->map(fn (HanseMerkurDocumentResponseEntity $document): InsuranceOfferDocumentLinkDto => new InsuranceOfferDocumentLinkDto(
                label: $this->documentLabel($document->documentType),
                url: $document->url,
                type: $document->documentType?->value,
            ))
            ->values()
            ->all();
    }

    private function documentLabel(?HanseMerkurDocumentTypeEnum $documentType): string
    {
        return $documentType?->isIpid() === true
            ? 'Informationsblatt zu den Versicherungsprodukten'
            : 'Allgemeine Versicherungsbedingungen';
    }
}
