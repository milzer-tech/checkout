<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the payment methods in Nezasa.
 *
 * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
 *
 * @method bool isCreditCard()
 * @method bool isBankTransfer()
 * @method bool isDirectDebit()
 * @method bool isPaypal()
 * @method bool isDatatrans()
 * @method bool isInvoice()
 * @method bool isPayyo()
 * @method bool isStripe()
 * @method bool isFuture()
 * @method bool isOther()
 * @method bool isUnknown()
 */
enum NezasaPaymentMethodEnum: string
{
    use PowerEnum;

    case CreditCard = 'CreditCard';
    case BankTransfer = 'BankTransfer';
    case DirectDebit = 'DirectDebit';
    case Paypal = 'Paypal';
    case Datatrans = 'Datatrans';
    case Invoice = 'Invoice';
    case Payyo = 'Payyo';
    case Stripe = 'Stripe';
    case Future = 'Future';
    case Other = 'Other';
    case Unknown = 'Unknown';
}
