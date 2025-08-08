<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the different types of components in the Nezasa Checkout API.
 *
 * @method bool isAccommodation()
 * @method bool isActivity()
 * @method bool isFlight()
 * @method bool isRentalCar()
 * @method bool isTransfer()
 * @method bool isInsurance()
 * @method bool isBaseService()
 * @method bool isUpsellItem()
 * @method bool isManual()
 * @method bool isCombined()
 * @method bool isTransport()
 */
enum ComponentEnum: string
{
    use PowerEnum;

    case Accommodation = 'Accommodation';
    case Activity = 'Activity';
    case Flight = 'Flight';
    case RentalCar = 'RentalCar';
    case Transfer = 'Transfer';
    case Insurance = 'Insurance';
    case BaseService = 'BaseService';
    case UpsellItem = 'UpsellItem';
    case Manual = 'Manual';
    case Combined = 'Combined';
    case Transport = 'Transport';
}
