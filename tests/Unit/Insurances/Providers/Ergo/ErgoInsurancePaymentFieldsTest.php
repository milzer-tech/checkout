<?php

declare(strict_types=1);

use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
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

it('provides the packaged ERGO logo', function (): void {
    expect(ErgoInsurance::getLogo())
        ->toStartWith('data:image/png;base64,');
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
