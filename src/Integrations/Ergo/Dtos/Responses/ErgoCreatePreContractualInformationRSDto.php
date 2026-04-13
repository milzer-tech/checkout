<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Responses;

use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoErrorsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Spatie\LaravelData\Data;

class ErgoCreatePreContractualInformationRSDto extends Data
{
    public function __construct(
        public string $MsgId,
        public string $EchoToken,
        public ?string $TransactionContext,
        public $TimeStamp,
        public string $Target,
        public $Success,
        public ?ErgoRequestorDto $Requestor,
        public ?string $PreContractualInformationID,
        public ?string $RecordOfAdviceURL,
        public ?array $PreContractualInformationServices,
        public ?string $Extensions,
        public ?ErgoErrorsTypeDto $Errors
    ) {}
}
