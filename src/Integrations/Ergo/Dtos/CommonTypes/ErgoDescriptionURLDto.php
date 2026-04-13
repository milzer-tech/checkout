<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

/**
 * {@see http://www.erv.de/eSoap/2019/09/common}DescriptionURLType — simpleContent (URI) with Type and DefaultInd.
 */
class ErgoDescriptionURLDto extends Data
{
    public function __construct(
        public mixed $DefaultInd,
        public string $Type,
        #[MapInputName('_')]
        public string $value,
    ) {}

    public function defaultInd(): bool
    {
        return filter_var($this->DefaultInd, FILTER_VALIDATE_BOOLEAN);
    }

    public function href(): string
    {
        return $this->value;
    }
}
