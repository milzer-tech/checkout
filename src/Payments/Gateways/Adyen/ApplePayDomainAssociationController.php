<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Adyen;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

/**
 * Serves Adyen's Apple Pay domain association file, which Apple requests to verify every domain that
 * offers Apple Pay with Adyen's certificate. The domain must also be added to Apple Pay in the Customer Area.
 *
 * Adyen updates the file from time to time, e.g. on May 16, 2024, and the verification fails with an old file,
 * so replace it with the one of the documentation when Adyen announces a new file.
 *
 * @see https://docs.adyen.com/payment-methods/apple-pay/web-drop-in#going-live
 */
class ApplePayDomainAssociationController
{
    private const string PATH = __DIR__.'/../../../Resources/adyen/apple-developer-merchantid-domain-association';

    public function __invoke(): Response
    {
        abort_unless(AdyenWidgetGateway::isActive(), 404);

        return new Response(File::get(self::PATH), 200, ['Content-Type' => 'text/plain']);
    }
}
