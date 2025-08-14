<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the types of transactions in Nezasa.
 *
 * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
 *
 * @method bool isPayment()
 * @method bool isRefund()
 */
enum NezasaTransactionTypeEnum: string
{
    use PowerEnum;

    case Payment = 'Payment';
    case Refund = 'Refund';
}
