<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Spatie\LaravelData\Data;

class PaxDetailEntity extends Data
{
    /**
     * Create a new instance of the PaxDetailEntity
     *
     * @link https://support.nezasa.com/hc/en-gb/articles/4404075693969-Planner-API
     *
     * @note There are other properties in this entity, but we don't need them for now.
     */
    public function __construct(
        public string $id,
        public ?int $age = null,
    ) {}
}
