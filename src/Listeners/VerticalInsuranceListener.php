<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Listeners;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Events\ItineraryBookingSucceededEvent;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Integrations\Vertical\Connectors\VerticalInsuranceConnector;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities\PurchasePaymentMethodPayloadEntity;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities\VerticalCustomerPayloadEntity;
use Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\PurchaseEventPayload;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Gateways\Stripe\StripeGateway;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

final class VerticalInsuranceListener
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
    public function handle(ItineraryBookingSucceededEvent $event): void
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
        } else {
            $this->revertVerticalInsurancePayment($verticalPaymentIntentId);
        }
    }

    /**
     * Determine if the event should be processed.
     */
    private function shouldBeProcessed(): bool
    {
        return $this->transaction->gateway === StripeGateway::name()
            && Config::boolean('checkout.insurance.vertical.active') === true
            && InsuranceCheckoutData::hasSelectedOffer(
                InsuranceCheckoutData::checkoutDataArray($this->transaction->checkout->data)
            );
    }

    /**
     * Get the payment method id from the payment intent stored in the session of Stripe.
     *
     * @throws ApiErrorException
     */
    private function getPaymentMethodId(): string
    {
        try {
            $paymentIntentId = $this->transaction->result_data['session']['payment_intent'];

            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['payment_intent' => $paymentIntent->toArray()],
            ]);

            return $paymentIntent->toArray()['payment_method'];
        } catch (\Throwable $e) {
            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['payment_intent' => 'could not be retrieved'],
            ]);

            throw $e;
        }
    }

    /**
     * Clone the payment method to the connected account.
     *
     * @throws ApiErrorException
     */
    private function clonePayment(string $paymentMethodId): string
    {
        try {
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
        } catch (\Throwable $e) {
            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['clone_method' => 'could not be cloned'],
            ]);

            throw $e;
        }
    }

    private function createVerticalPaymentIntent(string $paymentMethodId): string
    {
        try {
            $checkoutData = InsuranceCheckoutData::checkoutDataArray($this->transaction->checkout->data);
            $quote = InsuranceCheckoutData::getMeta($checkoutData);
            $newPaymentIntent = $this->stripe->paymentIntents->create(
                params: [
                    'payment_method' => $paymentMethodId,
                    'currency' => (string) $quote['currency'],
                    'amount' => (int) $quote['total'],
                    'off_session' => true,
                    'confirm' => true,
                    'metadata' => [
                        'quote_id' => (string) (InsuranceCheckoutData::getOffer($checkoutData)['id'] ?? ''),
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
        } catch (\Throwable $e) {
            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['new_payment_intend' => 'could not be created'],
            ]);

            throw $e;
        }
    }

    private function purchaseInsurance(string $paymentIntentId): bool
    {
        try {
            $checkoutData = InsuranceCheckoutData::checkoutDataArray($this->transaction->checkout->data);
            $offer = InsuranceCheckoutData::getOffer($checkoutData) ?? [];

            $response = VerticalInsuranceConnector::make()->purchase()->travel(
                new PurchaseEventPayload(
                    quote_id: (string) ($offer['id'] ?? ''),
                    payment_method: new PurchasePaymentMethodPayloadEntity(token: "stripe:$paymentIntentId"),
                    customer: new VerticalCustomerPayloadEntity(
                        first_name: $this->transaction->checkout->data['contact']['firstName'],
                        last_name: $this->transaction->checkout->data['contact']['lastName'],
                        email_address: $this->transaction->checkout->data['contact']['email']
                    )
                )
            );

            $this->transaction->pushToResultData([
                'insurance_purchase' => $response->array(),
                'insurance' => ['isSuccessful' => $response->successful()],
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['insurance_purchase' => 'could not be purchased'],
            ]);

            throw $e;
        }
    }

    /**
     * Release or refund the vertical insurance PaymentIntent on the connected account
     * when the Vertical purchase API call did not succeed.
     */
    private function revertVerticalInsurancePayment(string $verticalPaymentIntentId): void
    {
        $connectedAccountId = Config::string('checkout.insurance.vertical.connected_account_id');
        $requestOptions = ['stripe_account' => $connectedAccountId];

        $this->transaction->refresh();

        try {
            $intent = $this->stripe->paymentIntents->retrieve($verticalPaymentIntentId, null, $requestOptions);

            if (in_array($intent->status, ['requires_capture', 'requires_confirmation', 'requires_payment_method', 'requires_action'], true)) {
                $canceledIntent = $this->stripe->paymentIntents->cancel($verticalPaymentIntentId, null, $requestOptions);

                $this->transaction->update([
                    'result_data' => $this->transaction->result_data + [
                        'vertical_insurance_revert' => [
                            'isSuccessful' => $canceledIntent->status === 'canceled',
                            'payment_intent' => $canceledIntent->toArray(),
                        ],
                    ],
                ]);

                return;
            }

            if ($intent->status === 'canceled') {
                $this->transaction->update([
                    'result_data' => $this->transaction->result_data + [
                        'vertical_insurance_revert' => [
                            'isSuccessful' => true,
                            'payment_intent' => $intent->toArray(),
                        ],
                    ],
                ]);

                return;
            }

            if (in_array($intent->status, ['succeeded', 'processing'], true)) {
                $refund = $this->stripe->refunds->create(
                    ['payment_intent' => $verticalPaymentIntentId],
                    $requestOptions,
                );

                $this->transaction->update([
                    'result_data' => $this->transaction->result_data + [
                        'vertical_insurance_revert' => [
                            'isSuccessful' => in_array($refund->status, ['succeeded', 'pending'], true),
                            'refund' => $refund->toArray(),
                        ],
                    ],
                ]);

                return;
            }

            $this->transaction->update([
                'result_data' => $this->transaction->result_data + [
                    'vertical_insurance_revert' => [
                        'isSuccessful' => false,
                        'payment_intent' => $intent->toArray(),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->transaction->update([
                'result_data' => $this->transaction->result_data + [
                    'vertical_insurance_revert' => 'could not be reverted',
                ],
            ]);

            report($e);
        }
    }

    private function saveInsuranceOnNezasa(): void
    {
        try {
            $insurance = data_get($this->transaction->result_data, 'insurance_purchase');

            if (isset($insurance['id'])) {
                $response = NezasaConnector::make()->checkout()->addCustomInsurance(
                    checkoutId: $this->transaction->checkout->checkout_id,
                    payload: new AddCustomInsurancePayload(
                        name: $insurance['product']['promotional_header'],
                        netPrice: new Price(intval($insurance['total']) / 100, $insurance['currency']),
                        salesPrice: new Price(intval($insurance['total']) / 100, $insurance['currency']),
                        bookingStatus: AvailabilityEnum::Booked,
                        supplierName: 'ViCoverage',
                        supplierConfirmationNumber: $insurance['policy_number'],
                        description: $insurance['policy_number']
                    )
                );

                $this->transaction->update([
                    'result_data' => $this->transaction->result_data + ['nezasa_insurance_response' => $response->array()],
                ]);
            }
        } catch (\Throwable $e) {
            $this->transaction->update([
                'result_data' => $this->transaction->result_data + ['nezasa_insurance_response' => 'could not be saved'],
            ]);

            throw $e;
        }
    }
}
