<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Contracts;

use Nezasa\Checkout\Integrations\Nezasa\Enums\ComponentEnum;

interface NezasaComponentDtoContract
{
    /**
     * Get the unique identifier for the component.
     */
    public function getId(): string;

    /**
     * Get the type of the component.
     */
    public function getType(): ComponentEnum;
}
