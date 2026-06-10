<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Computop\Dtos\Payloads;

use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\CaptureInfoPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\ComputopAmountDto;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\CredentialOnFilePayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\OrderPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\PaymentPayloadEntity;
use Nezasa\Checkout\Integrations\Computop\Dtos\Payloads\Entities\UrlPayloadEntity;

class ComputopTokenPaymentPayload extends ComputopPaymentPayload
{
    /**
     * Create a new instance of ComputopTokenPaymentPayload.
     */
    public function __construct(
        string $transactionId,
        ComputopAmountDto $amount,
        OrderPayloadEntity $order,
        UrlPayloadEntity $urls,
        CaptureInfoPayloadEntity $capture = new CaptureInfoPayloadEntity,
        PaymentPayloadEntity $payment = new PaymentPayloadEntity,
        string $language = 'en',
        public CredentialOnFilePayloadEntity $credentialOnFile = new CredentialOnFilePayloadEntity,
    ) {
        parent::__construct($transactionId, $amount, $order, $urls, $capture, $payment, $language);
    }
}
