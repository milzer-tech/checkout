<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

final class TermsAndConditionsResponseEntity extends BaseDto
{
    /**
     * Create a new instance of TermsAndConditionsResponseEntity.
     *
     * @param  Collection<int, TextSectionResponseEntity>  $sections
     */
    public function __construct(
        public Collection $sections = new Collection
    ) {}

}
