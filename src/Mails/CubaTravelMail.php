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
        foreach (collect($checkout->data['paxInfo'])->flatten(1) as $index => $traveller) {
            $data = collect(PaxInfoPayloadEntity::from($traveller)->all())->except('showTraveller', 'refId');

            $data['travel_reason'] = config()->array('checkout::cuba-travel.reasons')[$traveller['travel_reason']];

            if (isset($data['address'])) {
                $address = array_filter($data['address']->toArray());

                if ($address === []) {
                    $data->forget('address');
                }
            }

            $this->travelers[$index] = $data->filter()->toArray();
        }

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('sender@example.com', 'Mojtaba'),
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
}
