<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Http\Request;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Handlers\WidgetCallBackHandler;

class PaymentResult extends BaseCheckoutComponent
{
    public function mount(Request $request): void
    {
        $model = Checkout::with('lastestTransaction')->whereCheckoutId($this->checkoutId)->firstOrFail();

        $result = resolve(WidgetCallBackHandler::class)->run($model, $request);

        dd(
            $result
        );
    }
}
