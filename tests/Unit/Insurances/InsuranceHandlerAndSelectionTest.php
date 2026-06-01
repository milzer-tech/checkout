<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Insurances\Dtos\InsurancePaymentFieldDto;
use Nezasa\Checkout\Insurances\Handlers\InsuranceHandler;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Insurances\Providers\HanseMerkur\HanseMerkurInsurance;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\AddCustomInsuranceRequest;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class StubInsuranceProviderForHandlerTest implements InsuranceContract
{
    public static bool $active = true;

    public static ?CreateInsuranceOffersDto $lastCreateOffersDto = null;

    public static ?BookInsuranceOfferDto $lastBookOfferDto = null;

    public static bool $bookResultSuccessful = true;

    public static function isActive(): bool
    {
        return self::$active;
    }

    public static function getName(): string
    {
        return 'Stub Insurance';
    }

    public static function getLogo(): string
    {
        return 'https://example.test/stub-insurance.svg';
    }

    public function getPaymentFields(): array
    {
        return [InsurancePaymentFieldDto::iban()];
    }

    public function getNoSelectionText(): string
    {
        return 'Stub no insurance';
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

    public function getOffers(CreateInsuranceOffersDto $createOffersDto): InsuranceOffersResult
    {
        self::$lastCreateOffersDto = $createOffersDto;

        return new InsuranceOffersResult(
            isSuccessful: true,
            offers: [
                new InsuranceOfferDto(
                    id: 'stub-offer',
                    title: 'Stub offer',
                    price: new Price(12.34, 'EUR'),
                    coverage: ['Medical'],
                ),
            ],
            meta: ['provider' => 'stub']
        );
    }

    public function bookOffer(BookInsuranceOfferDto $bookOfferDto): InsuranceBookOfferResult
    {
        self::$lastBookOfferDto = $bookOfferDto;

        return new InsuranceBookOfferResult(
            isSuccessful: self::$bookResultSuccessful,
            confirmationId: 'CONF-123',
            data: ['booked' => true]
        );
    }

    public function getNezasaPayload(AddCustomInsurancePayload $payload): AddCustomInsurancePayload
    {
        return $payload;
    }
}

function insuranceHandlerCheckout(array $insurance = []): Checkout
{
    return Checkout::factory()->create([
        'checkout_id' => uniqid('checkout-', true),
        'itinerary_id' => uniqid('itinerary-', true),
        'origin' => 'APP',
        'lang' => 'en',
        'data' => [
            'contact' => [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'gender' => GenderEnum::Female,
                'email' => 'jane@example.test',
                'country' => 'DE-Germany',
                'countryCode' => 'DE',
                'city' => 'Berlin',
                'postalCode' => '10115',
                'street1' => 'Main Street',
                'street2' => '42',
            ],
            'paxInfo' => [
                [
                    [
                        'firstName' => 'Jane',
                        'lastName' => 'Doe',
                        'gender' => GenderEnum::Female,
                        'birthDate' => ['year' => 1990, 'month' => 1, 'day' => 15],
                        'country' => 'DE-Germany',
                        'countryCode' => 'DE',
                    ],
                ],
            ],
            'insurance' => $insurance ?: null,
        ],
    ]);
}

function insuranceHandlerItinerary(): ItinerarySummary
{
    $price = new Price(1000.0, 'EUR');

    return new ItinerarySummary(
        price: new PriceResponse(
            discountedPackagePrice: $price,
            packagePrice: $price,
            totalPackagePrice: $price,
            downPayment: $price,
            openAmount: new Price(0.0, 'EUR'),
            externallyPaidCharges: new ExternallyPaidChargesResponseEntity(new Price(0.0, 'EUR')),
            showTotalPrice: $price,
            showPaymentPrice: $price,
        ),
        title: 'Test trip',
        startDate: CarbonImmutable::parse('2025-09-01'),
        endDate: CarbonImmutable::parse('2025-09-10'),
        adults: 1,
        destinationCountries: new Collection(['DE']),
    );
}

beforeEach(function (): void {
    MockClient::destroyGlobal();

    StubInsuranceProviderForHandlerTest::$active = true;
    StubInsuranceProviderForHandlerTest::$lastCreateOffersDto = null;
    StubInsuranceProviderForHandlerTest::$lastBookOfferDto = null;
    StubInsuranceProviderForHandlerTest::$bookResultSuccessful = true;

    Config::set('checkout.insurance_provider', [StubInsuranceProviderForHandlerTest::class]);
    Config::set('checkout.insurance.vertical.active', false);
    Config::set('checkout.insurance.hanse_merkur.active', false);
    Config::set('checkout.insurance.ergo.active', false);
});

it('selects exactly one active insurance provider and rejects conflicting providers', function (): void {
    $action = resolve(GetActiveInsuranceAction::class);

    expect($action->run())->toBeInstanceOf(StubInsuranceProviderForHandlerTest::class);

    Config::set('checkout.insurance_provider', [HanseMerkurInsurance::class, ErgoInsurance::class]);
    Config::set('checkout.insurance.hanse_merkur.active', true);
    Config::set('checkout.insurance.ergo.active', true);

    expect(fn () => $action->run())->toThrow(InvalidArgumentException::class, 'Only one insurance provider can be active at a time.');
});

it('treats Vertical as available but keeps its price out of the main payment', function (): void {
    StubInsuranceProviderForHandlerTest::$active = false;
    Config::set('checkout.insurance.vertical.active', true);

    $handler = resolve(InsuranceHandler::class);

    expect($handler->isAvailable())->toBeTrue()
        ->and($handler->shouldAddOfferPriceToPayment())->toBeFalse()
        ->and($handler->getPaymentFields())->toBe([])
        ->and($handler->getProviderName())->toBeNull()
        ->and($handler->getProviderLogo())->toBeNull();
});

it('creates provider offers and stores provider meta plus create-offer context on checkout data', function (): void {
    $checkout = insuranceHandlerCheckout();
    $handler = resolve(InsuranceHandler::class);

    $result = $handler->createOffers($checkout, insuranceHandlerItinerary());

    $checkout->refresh();
    $data = InsuranceCheckoutData::checkoutDataArray($checkout->data);

    expect($result->isSuccessful)->toBeTrue()
        ->and($result->offers)->toHaveCount(1)
        ->and($handler->getProviderName())->toBe('Stub Insurance')
        ->and($handler->getProviderLogo())->toBe('https://example.test/stub-insurance.svg')
        ->and(StubInsuranceProviderForHandlerTest::$lastCreateOffersDto)->toBeInstanceOf(CreateInsuranceOffersDto::class)
        ->and(InsuranceCheckoutData::getMeta($data))->toBe(['provider' => 'stub'])
        ->and(InsuranceCheckoutData::getCreateOffer($data))->toBeArray()
        ->and((float) data_get(InsuranceCheckoutData::getCreateOffer($data), 'totalPrice.amount'))->toBe(1000.0);
});

it('adds selected insurance to payment price only when the provider collects it through checkout payment', function (): void {
    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'stub-offer',
        'title' => 'Stub offer',
        'price' => ['amount' => 12.34, 'currency' => 'EUR'],
        'coverage' => [],
    ];
    $checkout = insuranceHandlerCheckout(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance']
    );

    $handler = resolve(InsuranceHandler::class);

    expect($handler->paymentPriceWithSelectedOffer(new Price(250.0, 'EUR'), $checkout->data)->amount)
        ->toBe(262.34);

    Config::set('checkout.insurance_provider', [ErgoInsurance::class]);
    Config::set('checkout.insurance.ergo.active', true);

    expect($handler->paymentPriceWithSelectedOffer(new Price(250.0, 'EUR'), $checkout->data)->amount)
        ->toBe(250.0);

    $offer = InsuranceOfferDto::from(
        InsuranceCheckoutData::getOffer(InsuranceCheckoutData::checkoutDataArray($checkout->data))
    );
    expect($handler->separatePaymentNoticeForSelectedOffer($offer))
        ->toBe('The insurance for 12.34 EUR is paid separately by SEPA direct debit.');
});

it('books the selected provider offer using stored offer, create-offer context, meta, and payment data', function (): void {
    $createOffer = new CreateInsuranceOffersDto(
        startDate: CarbonImmutable::parse('2025-09-01'),
        endDate: CarbonImmutable::parse('2025-09-10'),
        totalPrice: new Price(1000.0, 'EUR'),
        contact: ContactInfoPayloadEntity::from([
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'gender' => GenderEnum::Female,
            'email' => 'jane@example.test',
            'country' => 'DE-Germany',
            'countryCode' => 'DE',
            'city' => 'Berlin',
            'postalCode' => '10115',
            'street1' => 'Main Street',
            'street2' => '42',
        ]),
        paxInfo: new Collection([
            PaxInfoPayloadEntity::from([
                'refId' => 'pax-0',
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'gender' => GenderEnum::Female,
                'birthDate' => ['year' => 1990, 'month' => 1, 'day' => 15],
                'country' => 'DE-Germany',
                'countryCode' => 'DE',
            ]),
        ]),
        destinationCountries: new Collection(['DE']),
    );

    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'stub-offer',
        'title' => 'Stub offer',
        'price' => ['amount' => 12.34, 'currency' => 'EUR'],
        'coverage' => ['Medical'],
    ];
    $bucket[InsuranceCheckoutData::CREATE_OFFER] = $createOffer->toArray();
    $bucket[InsuranceCheckoutData::META] = ['provider' => 'stub'];
    $bucket[InsuranceCheckoutData::PAYMENT] = ['iban' => 'DE89370400440532013000'];

    $checkout = insuranceHandlerCheckout(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance']
    );
    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    StubInsuranceProviderForHandlerTest::$bookResultSuccessful = false;

    $handler = resolve(InsuranceHandler::class);
    $handler->bookOffer($transaction);

    $transaction->refresh();

    expect(StubInsuranceProviderForHandlerTest::$lastBookOfferDto)->toBeInstanceOf(BookInsuranceOfferDto::class)
        ->and(StubInsuranceProviderForHandlerTest::$lastBookOfferDto->selectedOffer->id)->toBe('stub-offer')
        ->and(StubInsuranceProviderForHandlerTest::$lastBookOfferDto->meta)->toBe(['provider' => 'stub'])
        ->and(StubInsuranceProviderForHandlerTest::$lastBookOfferDto->payment['iban'])->toBe('DE89370400440532013000')
        ->and($transaction->result_data['insurance']['isSuccessful'])->toBeFalse()
        ->and($transaction->result_data['insurance']['confirmationId'])->toBe('CONF-123');
});

it('records successful insurance bookings in Nezasa with offer coverage in the description', function (): void {
    $mockClient = MockClient::global([
        AddCustomInsuranceRequest::class => MockResponse::make(['created' => true], 200),
    ]);

    $createOffer = new CreateInsuranceOffersDto(
        startDate: CarbonImmutable::parse('2025-09-01'),
        endDate: CarbonImmutable::parse('2025-09-10'),
        totalPrice: new Price(1000.0, 'EUR'),
        contact: ContactInfoPayloadEntity::from([
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'gender' => GenderEnum::Female,
            'email' => 'jane@example.test',
            'country' => 'DE-Germany',
            'countryCode' => 'DE',
            'city' => 'Berlin',
            'postalCode' => '10115',
            'street1' => 'Main Street',
            'street2' => '42',
        ]),
        paxInfo: new Collection([
            PaxInfoPayloadEntity::from([
                'refId' => 'pax-0',
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'gender' => GenderEnum::Female,
                'birthDate' => ['year' => 1990, 'month' => 1, 'day' => 15],
                'country' => 'DE-Germany',
                'countryCode' => 'DE',
            ]),
        ]),
        destinationCountries: new Collection(['DE']),
    );

    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'stub-offer',
        'title' => 'Stub offer',
        'price' => ['amount' => 12.34, 'currency' => 'EUR'],
        'coverage' => ['Medical assistance', 'Trip cancellation', 'Medical assistance', ''],
    ];
    $bucket[InsuranceCheckoutData::CREATE_OFFER] = $createOffer->toArray();
    $bucket[InsuranceCheckoutData::META] = ['provider' => 'stub'];

    $checkout = insuranceHandlerCheckout(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance']
    );
    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    resolve(InsuranceHandler::class)->bookOffer($transaction);

    $mockClient->assertSent(function (AddCustomInsuranceRequest $request): bool {
        expect($request->body()->all())
            ->toMatchArray([
                'name' => 'Stub offer',
                'supplierName' => 'Stub Insurance',
                'supplierConfirmationNumber' => 'CONF-123',
                'description' => "Medical assistance\n- Trip cancellation",
            ]);

        return true;
    });
});

it('fails fast when stored insurance offer or create-offer context is missing before booking', function (): void {
    $handler = resolve(InsuranceHandler::class);
    $transaction = Transaction::create([
        'checkout_id' => insuranceHandlerCheckout()->id,
        'gateway' => 'Invoice',
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    expect(fn () => $handler->bookOffer($transaction))
        ->toThrow(RuntimeException::class, 'Checkout insurance offer is missing.');

    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $bucket[InsuranceCheckoutData::OFFER] = [
        'id' => 'stub-offer',
        'title' => 'Stub offer',
        'price' => ['amount' => 12.34, 'currency' => 'EUR'],
        'coverage' => [],
    ];
    $checkout = insuranceHandlerCheckout(
        InsuranceCheckoutData::prepareInsuranceUpdate($bucket)['insurance']
    );
    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Invoice',
        'amount' => 1000,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Captured,
    ]);

    expect(fn () => $handler->bookOffer($transaction))
        ->toThrow(RuntimeException::class, 'Checkout insurance create_offer context is missing.');
});
