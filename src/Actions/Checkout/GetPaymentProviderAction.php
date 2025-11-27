<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Payments\Contracts\PaymentContract;

class GetPaymentProviderAction
{
    /**
     * Get the payment provider options.
     *
     * @return array<int, PaymentOption>
     */
    public function run(): array
    {
        $result = [];

        /** @var class-string<PaymentContract> $gateway */
        foreach (Config::array('checkout.payment', []) as $gateway) {

            if (! in_array(PaymentContract::class, class_implements($gateway))) {
                throw new InvalidArgumentException(
                    "the payment initiation $gateway is not an instance of WidgetPaymentInitiation"
                );
            }

            if ($gateway::isActive()) {
                $result[] = new PaymentOption(
                    name: $gateway::name(),
                    encryptedGateway: Crypt::encrypt($gateway::name()),
                    encryptedClassName: Crypt::encrypt($gateway)
                );
            }
        }

        return $result;
    }
}
