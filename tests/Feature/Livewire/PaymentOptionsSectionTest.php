<?php

declare(strict_types=1);

use Livewire\Livewire;
use Nezasa\Checkout\Actions\Checkout\GetPaymentProviderAction;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\PriceResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RegulatoryInformationResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Livewire\PaymentOptionsSection;
use Nezasa\Checkout\Models\Checkout;

it('renders the Markdown lists of the payment explainer with list styles', function (): void {
    app()->instance(GetPaymentProviderAction::class, new class extends GetPaymentProviderAction
    {
        public function run(): array
        {
            return [new PaymentOption('Invoice', encrypt('Invoice'), encrypt('InvoiceGateway'))];
        }
    });

    $checkout = Checkout::factory()->create([
        'checkout_id' => 'checkout-4985',
        'itinerary_id' => 'itinerary-4985',
        'origin' => 'APP',
        'lang' => 'de',
        'data' => ['status' => Checkout::buildSectionStatus(), 'acceptedTerms' => []],
    ]);

    $paymentExplainer = <<<'MARKDOWN'
        Bitte beachten Sie bezüglich der Zahlung per Kreditkarte:
        * Unterstützte Kreditkarten sind: Mastercard, Visa.
        * Mit Vertragsabschluss wird eine Anzahlung in Höhe von 20% des Reisepreises fällig.

        1. Anzahlung
        2. Restzahlung
        MARKDOWN;

    Livewire::test(PaymentOptionsSection::class, [
        'model' => $checkout,
        'checkoutId' => $checkout->checkout_id,
        'itineraryId' => $checkout->itinerary_id,
        'origin' => $checkout->origin,
        'lang' => $checkout->lang,
        'isExpanded' => true,
        'price' => new PriceResponse(
            discountedPackagePrice: new Price(1000.0, 'EUR'),
            packagePrice: new Price(1000.0, 'EUR'),
            totalPackagePrice: new Price(1000.0, 'EUR'),
            downPayment: new Price(1000.0, 'EUR'),
            openAmount: new Price(0.0, 'EUR'),
            externallyPaidCharges: new ExternallyPaidChargesResponseEntity(new Price(0.0, 'EUR')),
            showTotalPrice: new Price(1000.0, 'EUR'),
            showPaymentPrice: new Price(1000.0, 'EUR'),
        ),
        'regulatoryInformation' => new RegulatoryInformationResponse(paymentExplainer: $paymentExplainer),
    ])
        ->assertSeeHtml('<p>Bitte beachten Sie bezüglich der Zahlung per Kreditkarte:</p>')
        ->assertSeeHtml('<li>Unterstützte Kreditkarten sind: Mastercard, Visa.</li>')
        ->assertSeeHtml('<li>Anzahlung</li>')
        ->assertSeeHtml('[&_ul]:list-disc [&_ul]:pl-5')
        ->assertSeeHtml('[&_ol]:list-decimal [&_ol]:pl-5');
});
