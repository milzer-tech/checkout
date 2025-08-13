<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Livewire;

use Illuminate\Http\Request;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Contracts\ReturnUrlHasInvalidQueryParamsForValidation;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaCallBack;

class PaymentResult extends BaseCheckoutComponent
{
    public function mount(Request $request): void
    {
        $callback = new OppwaCallBack;
        $igonreQuery = $callback instanceof ReturnUrlHasInvalidQueryParamsForValidation
            ? $callback->addedParamsToReturnedUrl($request)
            : [];

        if (! $request->hasValidSignatureWhileIgnoring($igonreQuery)) {
            abort(403, 'Invalid signature');
        }

        $this->model = Checkout::with('lastestTransaction')->whereCheckoutId($this->checkoutId)->firstOrFail();

        $callback = new OppwaCallBack;
        $result = $callback->check(request(), $this->model->lastestTransaction->prepare_data);

        $this->model->lastestTransaction->result_data = $result->persistentData;
        $this->model->lastestTransaction->status = $result->status->value;

        $this->model->lastestTransaction->save();

        dd($result);
    }
}
