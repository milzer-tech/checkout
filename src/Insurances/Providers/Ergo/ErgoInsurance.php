<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Providers\Ergo;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceTerms;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoAddressDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoAvailablePlanDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCoveredPersonDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCoveredTravelerDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCoveredTravelersDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCurrencyAmountGroupDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCustomerNameTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoDescriptionURLDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoDestinationTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoEmailsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoErrorsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceCustomerPreContractualInformationDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceCustomerTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceDetailDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoPersonNameDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoPlanSearchInsuranceCustomerDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoQuotedTariffDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoQuoteDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestServicesTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestServiceTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoSearchTravelersTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoSearchTravelerTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoServiceTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoTravelerAllocationsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoTripTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Enum\ErgoNamePrefixEnum;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoCreatePreContractualInformationRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsuranceBookRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsurancePlanSearchRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Responses\ErgoInsuranceBookRSDto;
use Nezasa\Checkout\Integrations\Ergo\ErgoConnector;
use Nezasa\Checkout\Integrations\Ergo\Requests\ErgoCreatePreContractualInformation;
use Nezasa\Checkout\Integrations\Ergo\Requests\ErgoInsuranceBook;
use Nezasa\Checkout\Integrations\Ergo\Requests\ErgoPlanSearch;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Spatie\LaravelData\Data;

final class ErgoInsurance implements InsuranceContract
{
    private const string PROVIDER_META_KEY = 'ergo_available_plan';

    public static function isActive(): bool
    {
        return Config::boolean('checkout.insurance.ergo.active');
    }

    public static function getName(): string
    {
        return Config::string('checkout.insurance.ergo.name');
    }

    public function getOffers(CreateInsuranceOffersDto $createOffersDto): InsuranceOffersResult
    {
        $connector = ErgoConnector::make();
        $payload = $this->buildPlanSearchPayload($createOffersDto);

        $request = new ErgoPlanSearch($payload);
        $response = $connector->send($request);

        if (! $response->successful()) {
            return new InsuranceOffersResult(
                isSuccessful: false,
                meta: ['http' => $response->status()],
                errorMessage: 'Ergo plan search HTTP error.',
            );
        }

        $rs = $request->createDtoFromResponse($response);

        if ($this->hasSoapErrors($rs->Errors)) {
            return new InsuranceOffersResult(
                isSuccessful: false,
                meta: ['response' => $rs->toArray()],
                errorMessage: $this->formatErrors($rs->Errors),
            );
        }

        $plans = collect($rs->AvailablePlans ?? []);
        $offers = [];

        /** @var ErgoAvailablePlanDto $plan */
        foreach ($plans as $plan) {
            if (! $plan instanceof ErgoAvailablePlanDto) {
                $plan = ErgoAvailablePlanDto::from($plan);
            }
            $price = $this->priceFromQuote($plan->Quote, $createOffersDto->totalPrice);
            $offers[] = new InsuranceOfferDto(
                id: $this->offerId($plan),
                title: $plan->PlanDetail->Title,
                price: $price,
                coverage: $this->coverageTitles($plan->Quote),
                providerMeta: [self::PROVIDER_META_KEY => $plan->toArray()],
                terms: $this->termsForPlan($plan),
            );
        }

        return new InsuranceOffersResult(
            isSuccessful: true,
            offers: $offers,
            meta: ['ergo' => $rs->toArray()],
        );
    }

    public function bookOffer(BookInsuranceOfferDto $bookOfferDto): InsuranceBookOfferResult
    {
        $planData = $bookOfferDto->selectedOffer->providerMeta[self::PROVIDER_META_KEY] ?? null;

        if (! is_array($planData)) {
            return new InsuranceBookOfferResult(
                isSuccessful: false,
                data: ['error' => 'Missing Ergo plan data on selected offer; quote again.'],
            );
        }

        $plan = ErgoAvailablePlanDto::from($planData);
        $created = $bookOfferDto->createdOfferDto;
        $contact = $created->contact;

        $quoteRef = (string) $plan->Quote->ID;

        $prePayload = new ErgoCreatePreContractualInformationRQDto(
            MsgId: Str::uuid()->toString(),
            TimeStamp: now(),
            CoveredTravelers: $this->buildCoveredTravelers($created->paxInfo),
            QuoteIDRef: $quoteRef,
            CoveredTrip: $this->buildTripDto($created),
            InsuranceCustomerPreContractualInformation: $this->buildPreContractualCustomer($contact),
            PreContractualInformationServices: $this->toRequestServices($plan),
            EmailPreContractualInformation: $this->emailsFor($contact->email),
        );

        $preRequest = new ErgoCreatePreContractualInformation($prePayload);
        $preResponse = ErgoConnector::make()->send($preRequest);

        if (! $preResponse->successful()) {
            return new InsuranceBookOfferResult(isSuccessful: false, data: ['http' => $preResponse->status()]);
        }

        $preRs = $preRequest->createDtoFromResponse($preResponse);

        if ($this->hasSoapErrors($preRs->Errors)) {
            return new InsuranceBookOfferResult(
                isSuccessful: false,
                data: ['response' => $preRs->toArray(), 'errors' => $this->formatErrors($preRs->Errors)],
            );
        }

        $preId = $preRs->PreContractualInformationID;
        if ($preId === null || $preId === '') {
            return new InsuranceBookOfferResult(
                isSuccessful: false,
                data: ['response' => $preRs->toArray()],
            );
        }

        $bookPayload = new ErgoInsuranceBookRQDto(
            MsgId: Str::uuid()->toString(),
            TimeStamp: now(),
            PreContractualInformationID: $preId,
            CoveredTravelers: $this->buildCoveredTravelers($created->paxInfo),
            QuoteIDRef: $quoteRef,
            CoveredTrip: $this->buildTripDto($created),
            InsuranceCustomer: $this->buildInsuranceCustomer($contact),
            BookServices: $this->toRequestServices($plan),
            EmailPolicy: $this->emailsFor($contact->email),
        );

        $bookRequest = new ErgoInsuranceBook($bookPayload);
        $bookResponse = ErgoConnector::make()->send($bookRequest);

        if (! $bookResponse->successful()) {
            return new InsuranceBookOfferResult(isSuccessful: false, data: ['http' => $bookResponse->status()]);
        }

        $bookRs = $bookRequest->createDtoFromResponse($bookResponse);

        if ($this->hasSoapErrors($bookRs->Errors)) {
            return new InsuranceBookOfferResult(
                isSuccessful: false,
                data: ['response' => $bookRs->toArray(), 'errors' => $this->formatErrors($bookRs->Errors)],
            );
        }

        $confirmation = $this->policyNumber($bookRs);

        return new InsuranceBookOfferResult(
            isSuccessful: true,
            confirmationId: $confirmation,
            data: [
                'pre_contractual' => $preRs->toArray(),
                'book' => $bookRs->toArray(),
            ],
        );
    }

    public function getNezasaPayload(AddCustomInsurancePayload $payload): AddCustomInsurancePayload
    {
        return $payload;
    }

    private function buildPlanSearchPayload(CreateInsuranceOffersDto $dto): ErgoInsurancePlanSearchRQDto
    {
        $msgId = Str::uuid()->toString();
        $echo = Config::get('checkout.insurance.ergo.echo_token') ?: $msgId;
        $tx = Config::get('checkout.insurance.ergo.transaction_context') ?: Str::uuid()->toString();

        $residence = $dto->contact->address?->getCountryCode() ?? 'DE';

        return new ErgoInsurancePlanSearchRQDto(
            MsgId: $msgId,
            EchoToken: is_string($echo) ? $echo : $msgId,
            TransactionContext: is_string($tx) ? $tx : '',
            TimeStamp: now(),
            CoveredTrip: $this->buildTripDto($dto),
            Travelers: $this->buildSearchTravelers($dto->paxInfo),
            InsuranceCustomer: new ErgoPlanSearchInsuranceCustomerDto(ResidenceCountryCode: $residence),
            AutoQuote: Config::boolean('checkout.insurance.ergo.auto_quote'),
            ListType: Config::string('checkout.insurance.ergo.list_type'),
        );
    }

    /**
     * @param  Collection<int, PaxInfoPayloadEntity>  $paxInfo
     */
    private function buildSearchTravelers(Collection $paxInfo): ErgoSearchTravelersTypeDto
    {
        $rows = $paxInfo->values()->map(function (PaxInfoPayloadEntity $pax, int $index): ErgoSearchTravelerTypeDto {
            $birth = $pax->birthDate ?? now();

            return new ErgoSearchTravelerTypeDto(
                ID: $index + 1,
                Birthdate: Carbon::parse($birth->format('Y-m-d')),
                Age: null,
                IndCoverageReqs: null,
                Extensions: null,
            );
        });

        return new ErgoSearchTravelersTypeDto(Traveler: $rows);
    }

    /**
     * @param  Collection<int, PaxInfoPayloadEntity>  $paxInfo
     */
    private function buildCoveredTravelers(Collection $paxInfo): ErgoCoveredTravelersDto
    {
        $rows = $paxInfo->values()->map(function (PaxInfoPayloadEntity $pax, int $index): ErgoCoveredTravelerDto {
            $birth = $pax->birthDate ?? now();

            return new ErgoCoveredTravelerDto(
                ID: $index + 1,
                CoveredPerson: new ErgoCoveredPersonDto(
                    PersonName: new ErgoPersonNameDto(
                        NamePrefix: ErgoNamePrefixEnum::fromNezasaGender($pax->gender),
                        GivenName: $pax->firstName ?? '',
                        Surname: $pax->lastName ?? '',
                    ),
                    Birthdate: Carbon::parse($birth->format('Y-m-d')),
                ),
            );
        });

        return new ErgoCoveredTravelersDto(CoveredTraveler: $rows);
    }

    private function buildTripDto(CreateInsuranceOffersDto $dto): ErgoTripTypeDto
    {
        $price = $dto->totalPrice;

        $countries = $dto->destinationCountries->map(
            fn ($c): string => str($c)->before('-')->toString()
        )->values()->all();

        return new ErgoTripTypeDto(
            StartDate: Carbon::parse($dto->startDate->format('Y-m-d')),
            EndDate: Carbon::parse($dto->endDate->format('Y-m-d')),
            Destination: new ErgoDestinationTypeDto(Country: $countries),
            BookingConfirmation: Carbon::now()->startOfDay(),
            TotalTripCost: new ErgoCurrencyAmountGroupDto(
                Amount: (string) ceil($dto->totalPrice->amount),
                CurrencyCode: $price->currency,
            ),
        );
    }

    private function buildPreContractualCustomer(ContactInfoPayloadEntity $contact): ErgoInsuranceCustomerPreContractualInformationDto
    {
        return new ErgoInsuranceCustomerPreContractualInformationDto(
            PersonName: new ErgoCustomerNameTypeDto(
                NamePrefix: ErgoNamePrefixEnum::fromNezasaGender($contact->gender),
                GivenName: $contact->firstName ?? '',
                Surname: $contact->lastName ?? '',
            ),
            Email: $contact->email ?? '',
            Address: $this->buildAddress($contact),
            Telephone: null,
            Mobile: $contact->mobilePhone,
            Fax: null,
        );
    }

    private function buildInsuranceCustomer(ContactInfoPayloadEntity $contact): ErgoInsuranceCustomerTypeDto
    {
        return new ErgoInsuranceCustomerTypeDto(
            PersonName: new ErgoCustomerNameTypeDto(
                NamePrefix: ErgoNamePrefixEnum::fromNezasaGender($contact->gender),
                GivenName: $contact->firstName ?? '',
                Surname: $contact->lastName ?? '',
            ),
            Email: $contact->email ?? '',
            Address: $this->buildAddress($contact),
            PaymentForm: null,
            Telephone: null,
            Mobile: $contact->mobilePhone,
        );
    }

    private function buildAddress(ContactInfoPayloadEntity $contact): ErgoAddressDto
    {
        $street = trim(($contact->address->street1 ?? '').' '.($contact->address->street2 ?? ''));

        return new ErgoAddressDto(
            StreetAndNr: $street !== '' ? $street : '—',
            CityName: $contact->address->city ?? '—',
            PostalCode: $contact->address->postalCode ?? '—',
            Country: $contact->address->getCountryCode() ?? 'DE',
        );
    }

    private function emailsFor(?string $email): ErgoEmailsTypeDto
    {
        if ($email === null || $email === '') {
            return new ErgoEmailsTypeDto(AdditionalEmail: []);
        }

        return new ErgoEmailsTypeDto(AdditionalEmail: [$email]);
    }

    private function toRequestServices(ErgoAvailablePlanDto $plan): ErgoRequestServicesTypeDto
    {
        $services = $plan->Quote->Services->Service;

        $mapped = $services->map(function (mixed $service): ErgoRequestServiceTypeDto {
            $dto = $service instanceof ErgoServiceTypeDto
                ? $service
                : ErgoServiceTypeDto::from($this->toArrayRecursive($service));

            $tariffCode = $this->tariffCodeFromService($dto);

            return new ErgoRequestServiceTypeDto(
                ID: (int) $dto->ID,
                QuotedTariff: new ErgoQuotedTariffDto(TariffCode: $tariffCode),
                TravelerAllocations: $dto->TravelerAllocations instanceof ErgoTravelerAllocationsTypeDto
                    ? $dto->TravelerAllocations
                    : ErgoTravelerAllocationsTypeDto::from($this->toArrayRecursive($dto->TravelerAllocations)),
            );
        });

        return new ErgoRequestServicesTypeDto(Service: $mapped);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArrayRecursive(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            /** @var array<string, mixed> $arr */
            $arr = $value->toArray();

            return $arr;
        }

        return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private function tariffCodeFromService(ErgoServiceTypeDto $service): string
    {
        $tariff = $service->Tariff;
        if ($tariff instanceof Data) {
            $arr = $tariff->toArray();
        } elseif (is_array($tariff)) {
            $arr = $tariff;
        } elseif (is_object($tariff)) {
            $arr = get_object_vars($tariff);
        } else {
            $arr = [];
        }

        $code = $arr['TariffCode'] ?? null;

        return is_string($code) ? $code : (string) $code;
    }

    private function priceFromQuote(ErgoQuoteDto $quote, Price $fallback): Price
    {
        $tp = $quote->Services->TotalPremium;
        $attrs = $this->currencyAmountAttributes($tp);

        if ($attrs === null) {
            return $fallback;
        }

        $amountRaw = $attrs['Amount'] ?? null;
        $currency = (string) ($attrs['CurrencyCode'] ?? $fallback->currency);
        $dp = (int) ($attrs['DecimalPlaces'] ?? 0);

        if ($amountRaw === null) {
            return new Price(amount: $fallback->amount, currency: $currency);
        }

        $numeric = is_numeric($amountRaw) ? (float) $amountRaw : 0.0;
        $amount = $dp > 0 ? $numeric / (10 ** $dp) : $numeric;

        return new Price(amount: $amount, currency: $currency);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function currencyAmountAttributes(mixed $totalPremium): ?array
    {
        if ($totalPremium === null) {
            return null;
        }

        if (is_array($totalPremium)) {
            if (isset($totalPremium['@attributes']) && is_array($totalPremium['@attributes'])) {
                return $totalPremium['@attributes'];
            }

            return $totalPremium;
        }

        if (is_object($totalPremium)) {
            return get_object_vars($totalPremium);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function coverageTitles(ErgoQuoteDto $quote): array
    {
        return $quote->InsuranceDetails->map(function (mixed $row): string {
            if ($row instanceof ErgoInsuranceDetailDto) {
                return $row->Title !== '' ? $row->Title : $row->Code;
            }
            if ($row instanceof Data) {
                $t = $row->toArray();

                return (string) ($t['Title'] ?? $t['Code'] ?? '');
            }
            if (is_array($row)) {
                return (string) ($row['Title'] ?? $row['Code'] ?? '');
            }

            return (string) ($row->Title ?? $row->Code ?? '');
        })->filter()->values()->all();
    }

    private function termsForPlan(ErgoAvailablePlanDto $plan): InsuranceTerms
    {
        $links = [];

        foreach ($plan->PlanDetail->DescriptionURL as $url) {
            $href = match (true) {
                $url instanceof ErgoDescriptionURLDto => $url->href(),
                is_string($url) => $url,
                default => (string) $url,
            };
            if ($href !== '') {
                $safe = htmlspecialchars($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $links[] = '<a target="_blank" rel="noopener" href="'.$safe.'">'.$safe.'</a>';
            }
        }

        return new InsuranceTerms(
            checkboxText: 'Ich habe die Produktinformationen und Vorschlagsinformationen zur Kenntnis genommen.',
            conditions: $links,
        );
    }

    private function offerId(ErgoAvailablePlanDto $plan): string
    {
        return 'ergo_'.$plan->PlanCode.'_'.$plan->Quote->ID;
    }

    private function hasSoapErrors(?ErgoErrorsTypeDto $errors): bool
    {
        return $errors instanceof ErgoErrorsTypeDto && $errors->Error->isNotEmpty();
    }

    private function formatErrors(?ErgoErrorsTypeDto $errors): ?string
    {
        if (! $errors instanceof ErgoErrorsTypeDto) {
            return null;
        }

        return $errors->Error->map(fn ($e): string => ($e->ErrorCode ?? '').': '.($e->ErrorMessage ?? ''))->implode('; ');
    }

    private function policyNumber(ErgoInsuranceBookRSDto $rs): ?string
    {
        $pd = $rs->PolicyDetail;
        if (! is_array($pd)) {
            return null;
        }

        return isset($pd['PolicyNumber']) ? (string) $pd['PolicyNumber'] : null;
    }
}
