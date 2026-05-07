<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Dtos\Contracts\NezasaComponentDtoContract;
use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;

it('returns the package root path', function (): void {
    expect(checkout_path())->toBe(dirname(__DIR__, 2));
});

it('returns a package relative path', function (): void {
    expect(checkout_path('/config/checkout.php'))
        ->toBe(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'config/checkout.php');
});

it('returns the itinerary url to the trip builder', function (): void {
    Config::set('checkout.nezasa.base_url', 'https://tripbuilder.test');
    app()->instance('request', Request::create('/checkout', 'GET', [
        'itineraryId' => 'itinerary-123',
    ]));

    expect(getUrlToTripBuilder())->toBe('https://tripbuilder.test/itineraries/itinerary-123');
});

it('returns the smartplanner url to the trip builder', function (): void {
    Config::set('checkout.nezasa.base_url', 'https://tripbuilder.test');
    app()->instance('request', Request::create('/checkout', 'GET', [
        'goto' => 'smartplanner',
        'itineraryId' => 'itinerary-123',
    ]));

    expect(getUrlToTripBuilder())
        ->toBe('https://tripbuilder.test/itinerary-apps/smartplanner/itinerary-123?goto=smartplanner');
});

it('returns the regular trip builder url when replacing a component outside smartplanner', function (): void {
    Config::set('checkout.nezasa.base_url', 'https://tripbuilder.test');
    app()->instance('request', Request::create('/checkout', 'GET', [
        'itineraryId' => 'itinerary-123',
    ]));

    $component = new class implements NezasaComponentDtoContract
    {
        public function getId(): string
        {
            return 'activity-123';
        }

        public function getType(): ComponentEnum
        {
            return ComponentEnum::Activity;
        }
    };

    expect(getUrlToReplaceComponent($component))
        ->toBe('https://tripbuilder.test/itineraries/itinerary-123');
});

it('returns the smartplanner drawer url when replacing a non-transport component', function (): void {
    Config::set('checkout.nezasa.base_url', 'https://tripbuilder.test');
    app()->instance('request', Request::create('/checkout', 'GET', [
        'goto' => 'smartplanner',
        'itineraryId' => 'itinerary-123',
    ]));

    $component = new class implements NezasaComponentDtoContract
    {
        public function getId(): string
        {
            return 'activity-123';
        }

        public function getType(): ComponentEnum
        {
            return ComponentEnum::Activity;
        }
    };

    expect(getUrlToReplaceComponent($component))
        ->toBe('https://tripbuilder.test/itinerary-apps/smartplanner/itinerary-123?goto=smartplanner&openDrawer=activity&componentId=activity-123');
});

it('uses the flight drawer when replacing a transport component in smartplanner', function (): void {
    Config::set('checkout.nezasa.base_url', 'https://tripbuilder.test');
    app()->instance('request', Request::create('/checkout', 'GET', [
        'goto' => 'smartplanner',
        'itineraryId' => 'itinerary-123',
    ]));

    $component = new class implements NezasaComponentDtoContract
    {
        public function getId(): string
        {
            return 'transport-123';
        }

        public function getType(): ComponentEnum
        {
            return ComponentEnum::Transport;
        }
    };

    expect(getUrlToReplaceComponent($component))
        ->toBe('https://tripbuilder.test/itinerary-apps/smartplanner/itinerary-123?goto=smartplanner&openDrawer=flight&componentId=transport-123');
});
