<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

use Nezasa\Checkout\Dtos\View\ShowTraveller;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\CountriesResponse;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Jobs\SaveTraverDetailsJob;

class TravellerSupporter
{
    /**
     * Sets the first traveller to be shown if no travellers are currently showing.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $paxInfo
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function setShowingTravellers(array $paxInfo): array
    {
        foreach (array_keys($paxInfo) as $roomNumber) {
            if (collect($paxInfo[$roomNumber])
                ->pluck('showTraveller')
                ->filter(fn (ShowTraveller $item): bool => $item->isShowing)
                ->isNotEmpty()
            ) {
                return $paxInfo;
            }
        }

        $paxInfo[0][0]['showTraveller']->isShowing = true;

        return $paxInfo;
    }

    /**
     * Checks if the traveller form is completed.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $paxInfo
     */
    public static function isFormCompleted(array $paxInfo): bool
    {
        return collect($paxInfo)
            ->flatten(1)
            ->pluck('showTraveller')
            ->transform(fn ($item): ShowTraveller => ShowTraveller::from($item))
            ->reject(fn (ShowTraveller $item): bool => $item->isFilled)
            ->isEmpty();
    }

    /**
     * Defines default values for the traveller data.
     *
     * @param  array<string, mixed>  $pax
     */
    protected static function defineDefaultValues(array &$pax, CountriesResponse $countriesResponse): void
    {
        $defaultCountry = $countriesResponse->countries->firstWhere('preferred', true);

        if ($defaultCountry && ! isset($pax['country'])) {
            $pax['country'] = "$defaultCountry->iso_code-$defaultCountry->name";
        }
    }

    /**
     * Sets up the traveller data for the checkout.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $paxInfo
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function setUpPaxData(PaxAllocationResponseEntity $allocatedPax, array $paxInfo, CountriesResponse $countriesResponse): array
    {
        $paxNumber = 0;
        $result = [];

        foreach ($allocatedPax->rooms as $number => $room) {
            for ($i = 0; $i < $room->adults; $i++) {

                $result[$number][$i] = $paxInfo[$number][$i] ?? [];
                self::defineDefaultValues($result[$number][$i], $countriesResponse);

                if (! isset($result[$number][$i]['showTraveller'])) {
                    $result[$number][$i]['showTraveller'] = new ShowTraveller(isAdult: true);
                } else {
                    $result[$number][$i]['showTraveller'] = ShowTraveller::from($paxInfo[$number][$i]['showTraveller']);
                }

                $result[$number][$i]['refId'] = "pax-$paxNumber";

                $paxNumber++;
            }

            foreach ($room->childAges as $index => $age) {
                $result[$number][$index + $i] = $paxInfo[$number][$index + $i] ?? [];
                self::defineDefaultValues($result[$number][$i], $countriesResponse);

                if (! isset($result[$number][$index + $i]['showTraveller'])) {
                    $result[$number][$index + $i]['showTraveller'] = new ShowTraveller(isAdult: false, age: $age);
                } else {
                    $result[$number][$index + $i]['showTraveller'] = ShowTraveller::from($paxInfo[$number][$index + $i]['showTraveller']);
                    $result[$number][$index + $i]['showTraveller']->age = $age;
                }

                $result[$number][$index + $i]['refId'] = "pax-$paxNumber";

                $paxNumber++;
            }
        }

        return $result;
    }

    /**
     * Saves the active traveller data.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $paxInfo
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function saveActiveTraveller(array $paxInfo, string $checkoutId, string|int $room, string|int $traveler): array
    {
        $paxInfo[$room][$traveler]['showTraveller']->isFilled = true;
        $paxInfo[$room][$traveler]['showTraveller']->isShowing = false;

        dispatch(
            new SaveTraverDetailsJob($checkoutId, "paxInfo.$room.$traveler", $paxInfo[$room][$traveler])
        );

        return $paxInfo;
    }

    /**
     * Saves the next inactive traveller data.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $paxInfo
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function nextInactiveTraveller(array $paxInfo, string $checkoutId, string|int $room, string|int $traveler): array
    {
        $paxInfo[$room][$traveler]['showTraveller']->isFilled = false;
        $paxInfo[$room][$traveler]['showTraveller']->isShowing = true;

        dispatch(
            new SaveTraverDetailsJob($checkoutId, "paxInfo.$room.$traveler", $paxInfo[$room][$traveler])
        );

        return $paxInfo;
    }
}
