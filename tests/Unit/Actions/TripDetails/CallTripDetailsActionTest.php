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
