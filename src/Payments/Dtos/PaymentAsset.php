<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Dtos;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class PaymentAsset extends BaseDto
{
    /**
     * Create a new instance of PaymentAsset.
     *
     * @param  Collection<int, string>|array<int,string>  $scripts  ,  each string is a script tag.
     */
    public function __construct(
        public bool $isAvailable,
        public Collection|array $scripts = new Collection,
        public ?string $html = null,
    ) {
        if (is_array($this->scripts)) {
            $this->scripts = new Collection($this->scripts);
        }
    }
}
