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

        @if($price->downPercentOfTotal() < 100 && !$model->rest_payment)
            <div class="rounded-xl border border-blue-300 bg-blue-100 px-4 py-4">
                <div class="flex gap-3">
                    <div class="mt-0.5 shrink-0">
                        <svg class="h-5 w-5 text-blue-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9 9.25a.75.75 0 000 1.5h.25v4a.75.75 0 001.5 0v-4.75A.75.75 0 0010 9.25H9z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <div class="text-gray-900">
                        <p class="text-sm leading-6">
                            <span
                                class="font-semibold">{{trans('checkout::page.trip_details.you_will_pay_down_payment',['percentage' => $price->downPercentOfTotal().'%'])}}</span>
                            <span class="text-gray-700">
{{trans('checkout::page.trip_details.rest_payment_will_be_payable_later')}}
                    </span>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($model->rest_payment)
            <div class="rounded-xl border border-blue-300 bg-blue-100 px-4 py-4">
                <div class="flex gap-3">
                    <div class="mt-0.5 shrink-0">
                        <svg class="h-5 w-5 text-blue-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9 9.25a.75.75 0 000 1.5h.25v4a.75.75 0 001.5 0v-4.75A.75.75 0 0010 9.25H9z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <div class="text-gray-900">
                        <p class="text-sm leading-6">
                            <span
                                class="font-semibold">{{trans('checkout::page.trip_details.you_will_pay_remaining')}}</span>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-3">
            <h3 class="text-base font-semibold text-gray-900">{{trans('checkout::page.trip_details.conditions_of_payment')}}</h3>
        </div>
        <div class="space-y-3">
            {!!  str($regulatoryInformation->paymentExplainer)->markdown() !!}
        </div>

        <!-- Payment method -->
        <div class="space-y-4">
            <h3 class="text-base font-semibold text-gray-900">{{trans('checkout::page.trip_details.select_payment_methods')}}
                :</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($options as $option)
                    <label class="relative cursor-pointer payment-option">
                        <input
                            type="radio"
                            name="payment-option"
                            class="peer sr-only"
                            x-on:click="$wire.$parent.call('createPaymentPageUrl','{{ $option->encryptedGateway }}')"
                            @checked($option->isSelected)
                        >

                        <div class="flex h-16 items-center justify-between rounded-xl border bg-white px-4 shadow-sm
                                border-gray-200 hover:border-[#2681FF]
                                peer-checked:border-[#2681FF] peer-checked:ring-2 peer-checked:ring-[#2681FF]/20">

                            <div class="flex items-center gap-3">

                                <!-- Custom radio (outer circle + inner white ring + blue center) -->
                                <span class="relative flex h-6 w-6 items-center justify-center rounded-full border border-gray-400
                                         peer-checked:border-[#2681FF]">
                                <!-- inner white ring -->
                                <span class="flex h-4 w-4 items-center justify-center rounded-full bg-white">
                                    <!-- blue center -->
                                    <span class="dot h-2.5 w-2.5 rounded-full bg-[#2681FF] opacity-0"></span>
                                </span>
                            </span>

                                <span class="text-sm font-medium text-gray-900">
                                {{ $option->name }}
                            </span>

                            </div>
                        </div>

                        <style>
                            .payment-option input:checked + div .dot {
                                opacity: 1;
                            }
                        </style>
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
