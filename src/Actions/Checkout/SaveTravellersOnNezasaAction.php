<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Exceptions\NotStoredTravellerException;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\ContactInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\SaveTravellersDetailsPayload;
use Nezasa\Checkout\Models\Checkout;

final class SaveTravellersOnNezasaAction
{
    public function run(Checkout $model): void
    {
        /** @var Collection<int, PaxInfoPayloadEntity> $paxInfo */
        $paxInfo = new Collection;

        try {
            $model->refresh();
            /** @phpstan-ignore-next-line */
            foreach (collect($model->data['paxInfo'] ?? [])->flatten(1) as $index => $pax) {
                $paxInfo[] = PaxInfoPayloadEntity::from([
                    'refId' => "pax-$index",
                    ...$pax,
                ]);
            }

            $payload = new SaveTravellersDetailsPayload(
                contactInfo: ContactInfoPayloadEntity::from($model->data['contact']),
                paxInfo: $paxInfo
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
