<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\AddressEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;
use Nezasa\Checkout\Models\Checkout;

final class CubaTravelMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<int, array<string, string|GenderEnum|Carbon|AddressEntity>>
     */
    public array $travelers = [];

    /**
     * Create a new message instance.
     */
    public function __construct(public Checkout $checkout)
    {
        $this->fillTravellers($checkout);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: Config::string('checkout::cuba-travel.email.from'),
                name: Config::string('checkout::cuba-travel.email.from_name')
            ),
            subject: 'New Itinerary booked',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'checkout::mail.cuba-travel');
    }

    /**
     * Fill the travellers array.
     */
    public function fillTravellers(Checkout $checkout): void
    {
        /** @phpstan-ignore-next-line */
        foreach (collect($checkout->data['paxInfo'])->flatten(1) as $index => $traveller) {
            if (! isset($traveller['refId'])) {
                $traveller['refId'] = "pax-$index";
            }

            $data = collect(PaxInfoPayloadEntity::from($traveller)->all())
                ->except('showTraveller', 'refId')
                ->put('travel_reason', $traveller['travel_reason']);

            if (isset($data['address'])) {
                $address = array_filter($data['address']->toArray());

                if ($address === []) {
                    $data->forget('address');
                }
            }

            $this->travelers[] = $data->filter()->toArray();
        }
    }
}
