<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Exceptions\NotStoredTravellerException;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\SaveTravellersDetailsPayload;
use Nezasa\Checkout\Models\Checkout;

final class SaveTravellersOnNezasaAction
{
    public function run(Checkout $model): void
    {
        try {
            $model->refresh();

            $payload = new SaveTravellersDetailsPayload(
                contactInfo: $model->getContact(),
                paxInfo: $model->getPaxInfo(),
            );

            $response = resolve(NezasaConnector::class)
                ->checkout()
                ->saveTravelerDetails($model->checkout_id, $payload);

            if ($response->failed()) {
                $model->updateData([
                    'send' => $payload->toArray(),
                    'travelerDetailsError' => $response->array(),
                ]);

                throw new NotStoredTravellerException($response->array());
            }
        } catch (\Throwable $e) {
            throw new NotStoredTravellerException([$e->getMessage()]);
        }
    }
}
