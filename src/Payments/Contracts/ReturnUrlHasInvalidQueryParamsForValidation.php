<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;

interface ReturnUrlHasInvalidQueryParamsForValidation
{
    public function addedParamsToReturnedUrl(Request $request): array;
}
