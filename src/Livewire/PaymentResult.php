<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaCallBack;

class PaymentResult extends BaseCheckoutComponent
{
    public function mount(): void
    {
        $this->model = Checkout::with('lastestTransaction')->whereCheckoutId($this->checkoutId)->firstOrFail();

        $callback = new OppwaCallBack;
        $result = $callback->check(request(), $this->model->lastestTransaction->prepare_data);

        $this->model->lastestTransaction->result_data = $result->persistentData;
        $this->model->lastestTransaction->status = $result->status->value;

        $this->model->lastestTransaction->save();

    }
}
