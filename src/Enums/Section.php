<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing different sections in the checkout process.
 *
 * @method bool isPromo()
 * @method bool isContact()
 * @method bool isTraveller()
 */
enum Section: string
{
    use PowerEnum;

    case Promo = 'promo';
    case Contact = 'contact';
    case Traveller = 'traveller';
}
