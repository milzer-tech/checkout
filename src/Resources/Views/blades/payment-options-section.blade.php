@use(Illuminate\Support\Number)
@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.payment_options')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    onEdit="expand('{{Section::PaymentOptions->value}}')"
>
    <div class="space-y-6">

        @foreach($options as $option)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <label class="border border-blue-700 rounded-xl px-4 py-2 flex items-center gap-2 cursor-pointer hover:shadow-sm w-full h-[48px]">
                <input type="radio"
                       x-on:click="$wire.$parent.call('createPaymentPageUrl','{{encrypt($option->gateway->value)}}')"
                       @checked($option->isSelected)
                       name="payment-option"  class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                <span class="text-gray-900 font-medium text-sm">{{$option->name}}</span>
            </label>

        </div>
        @endforeach
    </div>
</x-checkout::editable-box>
