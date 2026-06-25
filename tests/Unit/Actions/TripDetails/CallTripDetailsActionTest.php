<?php

use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Dtos\Planner\RequiredResponses;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\EuPrrlLinkResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RegulatoryInformationResponse;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetAvailableUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\GetRequlatoryInformationRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\RetrieveCheckoutUpsellItemsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout\TravelerRequirementsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountriesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Location\CountryCodesRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\AddedRentalCarsRequest;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Faking\MockClient;

it('can retrieve a trip details', function (): void {
    MockClient::global([
        GetItineraryRequest::class => mockFixture('get_itinerary_response'),
        RetrieveCheckoutRequest::class => mockFixture('retrieve_checkout_response'),
        AddedRentalCarsRequest::class => mockFixture('added_rental_cars_response'),
        TravelerRequirementsRequest::class => mockFixture('traveller_requirements_response'),
        GetAvailableUpsellItemsRequest::class => mockFixture('get_available_upsell_items_response'),
        RetrieveCheckoutUpsellItemsRequest::class => mockFixture('retrieve_checkout_upsell_items_response'),
        CountryCodesRequest::class => mockFixture('country_codes_response'),
        CountriesRequest::class => mockFixture('countries_response'),
        GetRequlatoryInformationRequest::class => mockFixture('regulatory_information_response'),
    ]);

    $result = (new CallTripDetailsAction)->run(new CheckoutParamsDto(
        checkoutId: 'co-td-1',
        itineraryId: 'it-td-1',
        origin: 'app',
    ));

    expect($result)
        ->toBeObject()
        ->toBeInstanceOf(RequiredResponses::class)
        ->and($result->regulatoryInformation->euPrrl?->itineraryContentValidationEnabled)->toBeTrue()
        ->and($result->regulatoryInformation->euPrrl?->compliance?->compliant)->toBeTrue()
        ->and($result->regulatoryInformation->blocksCheckout())->toBeFalse();
});

it('maps EU-PRRL general terms confirmation fields from regulatory information', function (): void {
    $response = RegulatoryInformationResponse::from([
        'paymentExplainer' => 'Payments are handled securely.',
        'euPrrl' => [
            'generalTermsConfirmationEnabled' => true,
            'itineraryContentValidationEnabled' => true,
            'title' => 'EU package travel terms',
            'intro' => '<p>Confirm these terms before booking.</p>',
            'checkboxText' => 'I accept the EU package travel terms',
            'links' => [
                [
                    'url' => 'https://example.com/eu-prrl',
                    'linkText' => 'Read EU-PRRL information',
                ],
            ],
            'compliance' => [
                'compliant' => true,
                'reasons' => [],
            ],
        ],
    ]);

    expect($response->euPrrl?->generalTermsConfirmationEnabled)->toBeTrue()
        ->and($response->euPrrl?->title)->toBe('EU package travel terms')
        ->and($response->euPrrl?->intro)->toBe('<p>Confirm these terms before booking.</p>')
        ->and($response->euPrrl?->checkboxText)->toBe('I accept the EU package travel terms')
        ->and($response->euPrrl?->links)->toHaveCount(1)
        ->and($response->euPrrl?->links->first())->toBeInstanceOf(EuPrrlLinkResponseEntity::class)
        ->and($response->euPrrl?->links->first()?->url)->toBe('https://example.com/eu-prrl');
});

it('maps on-request confirmation fields from regulatory information', function (): void {
    $response = RegulatoryInformationResponse::from([
        'paymentExplainer' => 'Payments are handled securely.',
        'onRequest' => [
            'confirmationEnabled' => true,
            'confirmationText' => 'I understand this booking is on request.',
            'remarks' => '<p>This booking requires manual confirmation.</p>',
        ],
    ]);

    expect($response->onRequest?->confirmationEnabled)->toBeTrue()
        ->and($response->onRequest?->confirmationText)->toBe('I understand this booking is on request.')
        ->and($response->onRequest?->remarks)->toBe('<p>This booking requires manual confirmation.</p>')
        ->and($response->onRequest?->getConfirmationKey())->toBe(md5(json_encode([
            'confirmationText' => 'I understand this booking is on request.',
            'remarks' => '<p>This booking requires manual confirmation.</p>',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)));
});

it('maps travel information confirmation content from regulatory information', function (): void {
    $response = RegulatoryInformationResponse::from([
        'travelInformation' => [
            'confirmationEnabled' => true,
            'title' => 'General entry requirements',
            'intro' => '<p>Please check below Entry and Health Regulations that apply for the chosen destinations.</p>',
            'checkboxText' => 'I confirm that I read Entry and Health Regulations above',
        ],
    ]);

    expect($response->travelInformation?->confirmationEnabled)->toBeTrue()
        ->and($response->travelInformation?->title)->toBe('General entry requirements')
        ->and($response->travelInformation?->intro)->toBe('<p>Please check below Entry and Health Regulations that apply for the chosen destinations.</p>')
        ->and($response->travelInformation?->checkboxText)->toBe('I confirm that I read Entry and Health Regulations above');
});
