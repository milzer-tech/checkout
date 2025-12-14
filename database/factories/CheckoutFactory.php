<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nezasa\Checkout\Models\Checkout;

/**
 * @extends Factory<Checkout>
 */
final class CheckoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checkout_id' => 'test-check-id',
            'itinerary_id' => 'test-itinerary-id',
            'origin' => 'test-origin',
            'lang' => null,
            'data' => [
                'paxInfo' => [],
                'contact' => [],
                'activityAnswers' => [],
                'acceptedTerms' => [],
                'numberOfPax' => 2,
                'allocatedPax' => [
                    'rooms' => [
                        [
                            'adults' => 2,
                            'childAges' => [],
                        ],
                    ],
                ],
                'status' => Checkout::buildSectionStatus(),
                'insurance' => null,
            ],
        ];
    }
}
