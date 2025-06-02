<?php

use Nezasa\Checkout\Actions\Planner\SummarizeItineraryAction;
use Nezasa\Checkout\Integrations\Nezasa\Requests\Planner\GetItineraryRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('Summarizes an itinerary', function () {
    $this->fakeCarbon();

    MockClient::global([
        GetItineraryRequest::class => MockResponse::fixture('get_itinerary_response'),
    ]);

    $summerizeItinerary = resolve(SummarizeItineraryAction::class);

    $result = $summerizeItinerary->handle('test');

    $this->assertMatchesJsonSnapshot($result->toJson());
});
