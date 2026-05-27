<?php

declare(strict_types=1);

use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoAvailablePlanDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;

it('declares IBAN as required payment data', function (): void {
    $fields = (new ErgoInsurance)->getPaymentFields();

    expect($fields)->toHaveCount(1)
        ->and($fields[0]->key)->toBe('iban')
        ->and($fields[0]->type)->toBe('iban')
        ->and($fields[0]->required)->toBeTrue()
        ->and($fields[0]->sectionIntro)->toBe('Bitte geben Sie für die Zahlung der Versicherungsprämie ihre IBAN an. Die Versicherungsprämie wird direkt von der ERGO Reiseversicherung eingezogen.');
});

it('provides ERGO-specific no selection text', function (): void {
    expect((new ErgoInsurance)->getNoSelectionText())
        ->toBe('Ich verzichte auf einen Reiseschutz für mich und sämtliche Reiseteilnehmer. Das Risiko und die Kosten im Schadensfall trage ich selbst.');
});

it('provides the packaged ERGO logo', function (): void {
    expect(ErgoInsurance::getLogo())
        ->toStartWith('data:image/png;base64,');
});

it('maps ERGO product components to coverage advantages', function (): void {
    $plan = ErgoAvailablePlanDto::from([
        'PlanCode' => 'VK25RSM',
        'Ordering' => 1,
        'PlanDetail' => [
            'Title' => 'RundumSorglos-Schutz',
            'DescriptionURL' => [],
        ],
        'Quote' => [
            'ID' => 1,
            'Services' => [
                'Service' => [],
                'TotalPremium' => ['Amount' => '5300', 'CurrencyCode' => 'EUR', 'DecimalPlaces' => 2],
            ],
            'InsuranceDetails' => [
                'InsuranceDetail' => [
                    [
                        'Code' => 'JRV',
                        'Title' => 'für alle Reisen im Versicherungsjahr (Mindestlaufzeit 24 Monate, danach jährlich kündbar)',
                        'ProductComponents' => [
                            'ProductComponent' => [
                                ['@attributes' => ['Ordering' => 1, 'Name' => 'Stornokosten-Versicherung']],
                                ['Name' => 'Reiseabbruch-Versicherung'],
                            ],
                        ],
                    ],
                    [
                        'Code' => 'RK',
                        'Title' => 'für diese eine Reise (im ausgewählten Reisezeitraum gültig)',
                        'ProductComponents' => [
                            'ProductComponent' => ['Name' => 'Reisekranken-Versicherung'],
                        ],
                    ],
                ],
            ],
            'AcceptedPaymentTypes' => [],
        ],
    ]);

    $coverageTitles = new ReflectionMethod(ErgoInsurance::class, 'coverageTitles');

    expect($coverageTitles->invoke(new ErgoInsurance, $plan))->toBe([
        'Stornokosten-Versicherung',
        'Reiseabbruch-Versicherung',
        'Reisekranken-Versicherung',
        'für alle Reisen im Versicherungsjahr (Mindestlaufzeit 24 Monate, danach jährlich kündbar)',
        'für diese eine Reise (im ausgewählten Reisezeitraum gültig)',
    ]);
});

it('infers ERGO coverage advantages from live product descriptors when components are absent', function (): void {
    $plan = ErgoAvailablePlanDto::from([
        'PlanCode' => 'VK25RSM',
        'Ordering' => 1,
        'PlanDetail' => [
            'Title' => 'RundumSorglos-Schutz (mit Selbstbeteiligung)',
            'DescriptionURL' => [
                [
                    'DefaultInd' => true,
                    'Type' => 'INF',
                    '_' => 'https://www.ergo-reiseversicherung.de/de/produktinformationen/produktbeschreibungen/202504/kf/de/rs-schutz-msb',
                ],
            ],
        ],
        'Quote' => [
            'ID' => 1,
            'Services' => [
                'Service' => [
                    [
                        'ID' => 2,
                        'Tariff' => [
                            'TariffCode' => 'PNM107',
                            'TariffDescription' => [
                                'Title' => 'RundumSorglos-Schutz (mit Selbstbeteiligung)',
                                'DescriptionURL' => [
                                    ['Type' => 'PID', 'DefaultInd' => true, '_' => 'https://egate2.erv.de/escWeb/pib?tc=PNM107'],
                                ],
                            ],
                        ],
                    ],
                ],
                'TotalPremium' => ['Amount' => '9000', 'CurrencyCode' => 'EUR', 'DecimalPlaces' => 2],
            ],
            'InsuranceDetails' => [
                'InsuranceDetail' => [
                    'Code' => 'SIT',
                    'Title' => 'Einmalreise',
                    'ProductComponents' => null,
                ],
            ],
            'AcceptedPaymentTypes' => [],
        ],
    ]);

    $coverageTitles = new ReflectionMethod(ErgoInsurance::class, 'coverageTitles');

    expect($coverageTitles->invoke(new ErgoInsurance, $plan))->toBe([
        'Stornokosten-Versicherung',
        'Reiseabbruch-Versicherung',
        'Reisekranken-Versicherung',
        'Reisegepäck-Versicherung',
        'Einmalreise',
    ]);
});

it('keeps ERGO price outside the main payment and creates an open direct debit payload', function (): void {
    $subject = new ErgoInsurance;
    $transaction = new Transaction;
    $transaction->id = 123;
    $offer = new InsuranceOfferDto(
        id: 'ergo-offer',
        title: 'ERGO Travel Insurance',
        price: new Price(amount: 25.0, currency: 'EUR'),
        coverage: [],
    );
    $result = new InsuranceBookOfferResult(isSuccessful: true, confirmationId: 'POL-123');

    $payload = $subject->makeNezasaPaymentTransactionPayload($transaction, $offer, $result);

    expect($subject->shouldAddOfferPriceToPayment())->toBeFalse()
        ->and($subject->getSeparatePaymentNotice($offer))->toBe('The insurance for 25.00 EUR is paid separately by SEPA direct debit.')
        ->and($payload->externalRefId)->toBe('POL-123')
        ->and($payload->amount)->toBe($offer->price)
        ->and($payload->paymentMethod)->toBe(NezasaPaymentMethodEnum::Other)
        ->and($payload->status)->toBe(NezasaTransactionStatusEnum::Closed);
});
