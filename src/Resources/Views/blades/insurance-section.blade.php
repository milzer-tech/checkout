@use(Nezasa\Checkout\Enums\Section)
@use(Illuminate\Support\Facades\Config)
@use(Nezasa\Checkout\Supporters\InsuranceSupporter)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.travel_insurance')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{InsuranceSupporter::isAvailable() ? '' : 'hidden'}}"
    onEdit="expand('{{Section::Insurance->value}}')"

>
    <div class="space-y-4">

        @if(InsuranceSupporter::isAvailable() && $contact)

            @if(Config::boolean('checkout.insurance.vertical.active'))
                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/@vertical-insure/embedded-offer"></script>
                @endpush
                <div id="insurance_offer" wire:ignore wire:key="insurance_offer"></div>
                <div wire:ignore wire:key="insurance_script">
                    <script>
                        new VerticalInsure("#insurance_offer", {
                            client_id: "{{config()->string('checkout.insurance.vertical.username')}}",

                            product_config: {
                                "travel": [{
                                    "customer": {
                                        "first_name": "{{$contact->firstName}}",
                                        "last_name": "{{$contact->lastName}}",
                                        "email_address": "{{$contact->email}}",
                                        state: "MN",
                                        country: "US"
                                        {{--"street": "{{$contact->address->street1}} {{$contact->address->street2}}",--}}
                                        {{--"city": "{{$contact->address->city}}",--}}
                                        {{--"postal_code": "{{$contact->address->postalCode}}",--}}
                                        {{--"country": "{{str($contact->address->country)->beforeLast('-')->toString()}}"--}}
                                    },
                                    "attributes": {
                                        "trip_start_date": "{{$itinerary->startDate->toDateString()}}",
                                        "trip_end_date": "{{$itinerary->endDate->toDateString()}}",
                                        "destination_countries": {!! $itinerary->destinationCountries->toJson() !!},
                                        "trip_cost": {{$itinerary->price->discountedPackagePrice->toCent()}},
                                        "trip_cost_currency": "{{$itinerary->price->discountedPackagePrice->currency}}"
                                    },
                                    "currency": "{{$itinerary->price->discountedPackagePrice->currency}}"
                                }],
                            }

                        });

                        window.addEventListener("offer-state-change", (e) => {
                            @this.call('handleInsuranceQuote', e.detail.quotes[0]);
                        });
                    </script>
                </div>
            @endif
        @endif

        <div class="space-y-4 mt-8">
            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

            <div class="flex justify-between items-center">
                <div></div>
                <button type="button" wire:click="next"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    Next
                </button>
            </div>
        </div>

    </div>
</x-checkout::editable-box>
