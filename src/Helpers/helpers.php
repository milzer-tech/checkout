<?php

declare(strict_types=1);

use Nezasa\Checkout\Dtos\Contracts\NezasaComponentDtoContract;

if (! function_exists('checkout_path')) {
    /**
     * Get the root path of the package.
     */
    function checkout_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);

        $path = ltrim($path, '/\\');

        return $path === ''
            ? $root
            : $root.DIRECTORY_SEPARATOR.$path;
    }
}

if (! function_exists('getUrlToTripBuilder')) {
    /**
     * Get the url to the trip builder.
     */
    function getUrlToTripBuilder(): string
    {
        $baseUrl = request()->query('origin') === 'IBE'
            ? config('checkout.nezasa.ibe_base_url')
            : config('checkout.nezasa.base_url');

        $itineraryId = request()->query('itineraryId');

        return request()->query('goto') === 'smartplanner'
            ? $baseUrl.'/itinerary-apps/smartplanner/'.$itineraryId.'?goto=smartplanner'
            : $baseUrl.'/itineraries/'.$itineraryId;
    }
}

if (! function_exists('getUrlToReplaceComponent')) {
    /**
     * Get the url to replace the component.
     */
    function getUrlToReplaceComponent(NezasaComponentDtoContract $componentDto): string
    {
        $type = $componentDto->getType()->isTransport() ? 'flight' : $componentDto->getType()->toLower();

        return request()->query('goto') === 'smartplanner'
            ? getUrlToTripBuilder().'&openDrawer='.$type.'&componentId='.$componentDto->getId()
            : getUrlToTripBuilder();
    }
}
