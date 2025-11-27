<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\View;

use Illuminate\Support\Facades\Crypt;
use Nezasa\Checkout\Dtos\BaseDto;

class PaymentOption extends BaseDto
{
    /**
     * The payment option ID.
     */
    public function __construct(
        public string $name,
        public string $encryptedGateway,
        public string $encryptedClassName,
        public bool $isSelected = false,
    ) {}

    /**
     * Decrypt the gateway name.
     */
    public function decryptGateway(): string
    {
        return Crypt::decrypt($this->encryptedGateway);
    }

    /**
     * Decrypt the class name.
     */
    public function decryptClassName(): string
    {
        return Crypt::decrypt($this->encryptedClassName);
    }
}
