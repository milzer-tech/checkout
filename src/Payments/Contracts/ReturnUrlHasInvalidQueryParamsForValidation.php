<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;

interface ReturnUrlHasInvalidQueryParamsForValidation
{
    /**
     * Returns the list of query parameters that were added to the return URL after payment.
     *
     * @return array<string>
     */
    public function addedParamsToReturnedUrl(Request $request): array;
}
