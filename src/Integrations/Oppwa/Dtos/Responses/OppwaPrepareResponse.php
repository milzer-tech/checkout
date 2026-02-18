<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Oppwa\Dtos\Responses\Entities\OppwaResultResponseEntity;

final class OppwaPrepareResponse extends BaseDto
{
    /**
     * Create a new instance of OppwaPrepareResponse.
     */
    public function __construct(
        public string $id,
        public string $integrity,
        public string $ndc,
        public string $buildNumber,
        public CarbonImmutable $timestamp,
        public OppwaResultResponseEntity $result
    ) {}
}
