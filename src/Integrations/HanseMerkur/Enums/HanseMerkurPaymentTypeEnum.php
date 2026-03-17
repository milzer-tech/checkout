<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the payment types in the HanseMerkur Checkout API.
 *
 * @link https://payment-test.hmrv.de/rest/swagger-ui/index.html#/Payment/payV1
 *
 * @method bool isAgencyEncashment()
 * @method bool isCompanyEncashment()
 * @method bool isCreditCard()
 * @method bool isDirectBilling()
 * @method bool isSepa()
 */
enum HanseMerkurPaymentTypeEnum: string
{
    use PowerEnum;

    case AgencyEncashment = 'AGENCY_ENCASHMENT';
    case CompanyEncashment = 'COMPANY_ENCASHMENT';
    case CreditCard = 'CREDIT_CARD';
    case DirectBilling = 'DIRECT_BILLING';
    case Sepa = 'SEPA';
}
