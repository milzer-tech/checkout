<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Nezasa\Checkout\Dtos\View\PaymentOption;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentCallBack;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentInitiation;

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

        /** @var class-string<WidgetPaymentInitiation> $initiation */
        /** @var class-string<WidgetPaymentCallBack> $callback */
        foreach (Config::array('checkout.payment.widget', []) as $initiation => $callback) {
            if (! in_array(WidgetPaymentInitiation::class, class_implements($initiation))) {
                throw new InvalidArgumentException("the payment initiation $initiation is not an instance of WidgetPaymentInitiation");
            }

            if (! in_array(WidgetPaymentCallBack::class, class_implements($callback))) {
                throw new InvalidArgumentException("the payment callback $callback is not an instance of WidgetPaymentCallBack");
            }

            if ($initiation::isActive()) {
                $result[] = new PaymentOption(
                    name: $initiation::name(),
                    encryptedGateway: encrypt($initiation::name())
                );
            }
        }

        return $result;
    }
}
