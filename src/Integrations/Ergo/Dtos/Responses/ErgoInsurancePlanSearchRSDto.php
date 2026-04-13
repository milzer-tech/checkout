<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Responses;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Casts\ErgoSoapDataCollectionCast;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoAvailablePlanDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoErrorsTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoSearchTravelerTypeDto;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ErgoInsurancePlanSearchRSDto extends Data
{
    public function __construct(
        public string $MsgId,
        public string $EchoToken,
        public ?string $TransactionContext,
        public $TimeStamp,
        public string $Target,
        public $Success,
        public ?ErgoRequestorDto $Requestor,
        #[MapInputName('Travelers.Traveler')]
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoSearchTravelerTypeDto::class, null, ['ID', 'Birthdate'], true)]
        public ?Collection $Travelers,
        #[MapInputName('AvailablePlans.AvailablePlan')]
        #[WithCast(ErgoSoapDataCollectionCast::class, ErgoAvailablePlanDto::class, null, ['PlanCode', 'Ordering'], true)]
        public ?Collection $AvailablePlans,
        public ?string $Extensions,
        public ?ErgoErrorsTypeDto $Errors
    ) {}
}
