<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class EuPrrlResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the EuPrrlResponseEntity.
     *
     * @param  Collection<int, EuPrrlLinkResponseEntity>  $links
     */
    public function __construct(
        public bool $generalTermsConfirmationEnabled = false,
        public bool $itineraryContentValidationEnabled = false,
        public ?string $title = null,
        public ?string $intro = null,
        public ?string $checkboxText = null,
        public Collection $links = new Collection,
        public ?EuPrrlComplianceResponseEntity $compliance = null,
    ) {}
}
