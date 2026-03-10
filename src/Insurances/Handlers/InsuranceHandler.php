<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Handlers;

use Exception;
use Illuminate\Support\Facades\Config;

final class InsuranceHandler
{
    /**
     * Indicate if any insurance provider is active.
     *
     * @throws Exception
     */
    public function isAvailable(): bool
    {
        $count = Config::collection('checkout.insurance')->pluck('active')->filter()->count();

        if ($count > 1) {
            throw new Exception('Only one insurance provider can be active at a time.');
        }

        return $count === 1;
    }
}
