<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Nezasa\Enums\TravelerRequirementFieldEnum;

interface HasVisibleFieldsContract
{
    /**
     * Get the visible fields of the entity.
     *
     * @return Collection<string, TravelerRequirementFieldEnum>
     */
    public function getVisibleFields(): Collection;
}
