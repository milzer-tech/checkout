<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

abstract class BaseDto extends Data implements Wireable
{
    use WireableData;
}
