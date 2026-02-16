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

    <div class="space-y-8">

        {{-- Info box --}}
        <div class="rounded-xl border border-blue-300 bg-blue-100 px-4 py-4">
            <div class="flex gap-3">
                <div class="mt-0.5 shrink-0">
                    {{-- info icon --}}
                    <svg class="h-5 w-5 text-blue-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9 9.25a.75.75 0 000 1.5h.25v4a.75.75 0 001.5 0v-4.75A.75.75 0 0010 9.25H9z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <div class="text-gray-900">
                    <p class="text-sm leading-6">
                        <span class="font-semibold">You will be paying 30% of down payment now.</span>
                        <span class="text-gray-700">
                        The rest of the payment will be payable 14 days before departure. You will receive a link to complete that payment.
                    </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Conditions --}}
        <div class="space-y-3">
            <h3 class="text-base font-semibold text-gray-900">Conditions of the payment</h3>

            <p class="text-sm text-gray-700">Please note the following regarding payment by credit card:</p>

            <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
                <li>Supported credit cards are: Mastercard, Visa.</li>
                <li>100% down payment for trips within the next 30 days.</li>
                <li>30% down payment for trips later than the next 30 days. For the remaining 70% you will receive a separate invoice which is payable 14 days before departure.</li>
                <li>The card limit must be sufficient.</li>
                <li>The owner of the credit card must be the merchant of record.</li>
            </ul>
        </div>

        {{-- Payment method --}}
        <div class="space-y-4">
            <h3 class="text-base font-semibold text-gray-900">Select the payment method:</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($options as $option)
                    <label class="group relative cursor-pointer">
                        <input
                            type="radio"
                            name="payment-option"
                            class="sr-only peer"
                            x-on:click="$wire.$parent.call('createPaymentPageUrl','{{ $option->encryptedGateway }}')"
                            @checked($option->isSelected)
                        >

                        <div class="flex h-16 items-center justify-between rounded-xl border bg-white px-4 shadow-sm
                                border-gray-200
                                hover:border-blue-300
                                peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-200">
                            <div class="flex items-center gap-3">
                                {{-- custom radio --}}
                                <span class="flex h-5 w-5 items-center justify-center rounded-full border border-gray-300
                                         peer-checked:border-blue-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-600 opacity-0 peer-checked:opacity-100"></span>
                            </span>

                                <span class="text-sm font-medium text-gray-900">
                                {{ $option->name }}
                            </span>
                            </div>

                            {{-- small "logo" placeholder (optional) --}}
                            <span class="text-xs font-semibold text-gray-500 border border-gray-200 rounded px-1.5 py-0.5">
                            {{ strtolower($option->name) }}
                        </span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

    </div>


    {{--    <div class="space-y-6">--}}

{{--        @foreach($options as $option)--}}
{{--            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">--}}
{{--                <label--}}
{{--                    class="border border-blue-700 rounded-xl px-4 py-2 flex items-center gap-2 cursor-pointer hover:shadow-sm w-full h-[48px]">--}}
{{--                    <input type="radio"--}}
{{--                           x-on:click="$wire.$parent.call('createPaymentPageUrl','{{$option->encryptedGateway}}')"--}}
{{--                           @checked($option->isSelected)--}}
{{--                           name="payment-option" class="h-4 w-4 text-blue-600 focus:ring-blue-500">--}}
{{--                    <span class="text-gray-900 font-medium text-sm">{{$option->name}}</span>--}}
{{--                </label>--}}

{{--            </div>--}}
{{--        @endforeach--}}
{{--    </div>--}}
</x-checkout::editable-box>
