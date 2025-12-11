<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Events\ItineraryBookingFailedEvent;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Vertical\Connectors\VerticalInsuranceConnector;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities\PurchasePaymentMethodPayloadEntity;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities\VerticalCustomerPayloadEntity;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\PurchaseEventPayload;
use Nezasa\Checkout\Models\Transaction;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

final class VerticalInsuranceListener implements ShouldQueue
{
    /**
     * The stripe client.
     */
    private StripeClient $stripe;

    /**
     * The transaction being processed.
     */
    private Transaction $transaction;

    /**
     * Handle the event.
     */
    public function handle(ItineraryBookingFailedEvent|ItineraryBookingSucceededEvent $event): void
    {
        $this->transaction = $event->transaction;

        if (! $this->shouldBeProcessed()) {
            return;
        }

        $this->stripe = new StripeClient(Config::string('checkout.integrations.stripe.secret_key'));

        $paymentMethodId = $this->getPaymentMethodId();

        $clonePaymentMethodId = $this->clonePayment($paymentMethodId);

        $verticalPaymentIntentId = $this->createVerticalPaymentIntent($clonePaymentMethodId);

        if ($this->purchaseInsurance($verticalPaymentIntentId)) {
            $this->saveInsuranceOnNezasa();
        }
    }

    /**
     * Determine if the event should be processed.
     */
    private function shouldBeProcessed(): bool
    {
        return $this->transaction->gateway === 'Stripe'
            && Config::boolean('checkout.insurance.vertical.active') === true
            && isset($this->transaction->checkout->data['insurance'])
            && is_array($this->transaction->checkout->data['insurance'])
            && isset($this->transaction->checkout->data['insurance']['quote_id']);
    }

    /**
     * Get the payment method id from the payment intent stored in the session of Stripe.
     *
     * @throws ApiErrorException
     */
    private function getPaymentMethodId(): string
    {
        $paymentIntentId = $this->transaction->result_data['session']['payment_intent'];

        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

        $this->transaction->update([
            'result_data' => $this->transaction->result_data + ['payment_intent' => $paymentIntent->toArray()],
        ]);

        return $paymentIntent->toArray()['payment_method'];
    }

    /**
     * Clone the payment method to the connected account.
     *
     * @throws ApiErrorException
     */
    private function clonePayment(string $paymentMethodId): string
    {
        $paymentMethod = $this->stripe->paymentMethods->create(
            params: [
                'customer' => (string) $this->transaction->result_data['session']['customer'],
                'payment_method' => $paymentMethodId,
            ],
            opts: [
                'stripe_account' => Config::string('checkout.insurance.vertical.connected_account_id'),
            ]
        );

        $this->transaction->update([
            'result_data' => $this->transaction->result_data + ['clone_method' => $paymentMethod->toArray()],
        ]);

        return $paymentMethod->toArray()['id'];
    }

    private function createVerticalPaymentIntent(string $paymentMethodId): string
    {
        $newPaymentIntent = $this->stripe->paymentIntents->create(
            params: [
                'payment_method' => $paymentMethodId,
                'currency' => (string) $this->transaction->checkout->data['insurance']['currency'],
                'amount' => (int) $this->transaction->checkout->data['insurance']['total'],
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'quote_id' => (string) $this->transaction->checkout->data['insurance']['quote_id'],
                ],
            ],
            opts: [
                'stripe_account' => Config::string('checkout.insurance.vertical.connected_account_id'),
            ]
        );

        $this->transaction->update([
            'result_data' => $this->transaction->result_data + ['new_payment_intend' => $newPaymentIntent->toArray()],
        ]);

        return $newPaymentIntent->toArray()['id'];
    }

    private function purchaseInsurance(string $paymentIntentId): bool
    {
        $response = VerticalInsuranceConnector::make()->purchase()->travel(
            new PurchaseEventPayload(
                quote_id: $this->transaction->checkout->data['insurance']['quote_id'],
                payment_method: new PurchasePaymentMethodPayloadEntity(token: "stripe:$paymentIntentId"),
                customer: new VerticalCustomerPayloadEntity(
                    first_name: $this->transaction->checkout->data['contact']['firstName'],
                    last_name: $this->transaction->checkout->data['contact']['lastName'],
                    email_address: $this->transaction->checkout->data['contact']['email']
                )
            )
        );

        $this->transaction->update([
            'result_data' => $this->transaction->result_data + ['insurance_purchase' => $response->array()],
        ]);

        return $response->status() === 200 || $response->status() === 201;
    }

    private function saveInsuranceOnNezasa(): void
    {
        $insurance = data_get($this->transaction->result_data, 'insurance_purchase');

        if (isset($insurance['id'])) {
            $response = NezasaConnector::make()->checkout()->addCustomInsurance(
                checkoutId: $this->transaction->checkout->checkout_id,
                payload: new AddCustomInsurancePayload(
                    name: $insurance['product']['promotional_header'],
                    netPrice: new Price(intval($insurance['total']) / 100, $insurance['currency']),
                    salesPrice: new Price(intval($insurance['total']) / 100, $insurance['currency']),
                    bookingStatus: AvailabilityEnum::Booked,
                    supplierConfirmationNumber: $insurance['policy_number'],
                    description: data_get($insurance, 'product.description')
                )
            );

            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['nezasa_insurance_response' => $response->array()],
            ]);
        }
    }
}
