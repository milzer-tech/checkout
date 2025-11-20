@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.travel_insurance')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    onEdit="expand('{{Section::Insurance->value}}')"

>
    <div class="space-y-4">

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/@vertical-insure/embedded-offer"></script>
        @endpush
        <div id="insurance_offer"></div>

        <script>
            new VerticalInsure("#insurance_offer", {
                client_id: "live_94EQR9SAVMVK2Z775B8JJE2MHDJLVZQW",
                product_config: {
                    "travel": [{
                        "customer": {
                            "first_name": "James",
                            "last_name": "Doe",
                        },
                        "attributes": {
                            "trip_start_date": "2026-01-28",
                            "trip_end_date": "2026-02-01",
                            "destination_countries": [
                                "FR"
                            ],
                            "trip_cost": 903000,
                            "trip_cost_currency": "EUR"
                        },
                        "currency": "EUR"
                    }],
                },
                "payments": {
                    "enabled": true,
                    "button": true
                },

            }, function (offerState) {
                console.log(offerState)
            });

            window.addEventListener("offer-state-change", (e) => {
                console.log("Offers:", JSON.stringify(e.detail.quotes));
            });
        </script>


        <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

        <div class="flex justify-between items-center">

            <button type="button"
                    wire:click="next"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                {{trans('checkout::page.trip_details.next')}}
            </button>
        </div>

    </div>
</x-checkout::editable-box>
