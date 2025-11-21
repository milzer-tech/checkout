<div class="flex flex-col min-h-screen">
    <h1 class="text-2xl font-bold mb-6">{{trans('checkout::page.trip_details.trip_details')}}</h1>
    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr] gap-8">
        <div class="space-y-6">
            <section class="space-y-6">
                <livewire:contact-details
                    :$contactRequirements
                    :$countryCodes
                    :$countriesResponse
                    :$model
                    :is-completed="$model->data['status']['contact']['isCompleted']"
                    :is-expanded="$model->data['status']['contact']['isExpanded']"
                />
                <livewire:traveler-details
                    :$allocatedPax
                    :$passengerRequirements
                    :$countryCodes
                    :$countriesResponse
                    :$model
                    :$itinerary
                    :is-completed="$model->data['status']['traveller']['isCompleted']"
                    :is-expanded="$model->data['status']['traveller']['isExpanded']"
                />

                @if($itinerary->activities->isNotEmpty())
                    <livewire:activity-section
                        :$model
                        :is-completed="$model->data['status']['activity']['isCompleted']"
                        :is-expanded="$model->data['status']['activity']['isExpanded']"
                    />
                @endif

                <livewire:promo-code-section
                    :$prices
                    :$model
                    :is-completed="$model->data['status']['promo']['isCompleted']"
                    :is-expanded="$model->data['status']['promo']['isExpanded']"
                />
                <livewire:additional-services-section
                    :$upsellItemsResponse
                    :$addedUpsellItems
                    :$model
                    :is-completed="$model->data['status']['additional_service']['isCompleted']"
                    :is-expanded="$model->data['status']['additional_service']['isExpanded']"
                />
                {{--                <livewire:insurance-section--}}
                {{--                    :$prices--}}
                {{--                    :$model--}}
                {{--                    :is-completed="$model->data['status']['insurance']['isCompleted']"--}}
                {{--                    :is-expanded="$model->data['status']['insurance']['isExpanded']"--}}
                {{--                />--}}

                <livewire:payment-options-section
                    :$model
                    :is-completed="$model->data['status']['payment-options']['isCompleted']"
                    :is-expanded="$model->data['status']['payment-options']['isExpanded']"
                />
                <div class="mt-6"></div>
            </section>
        </div>
        <div class="overflow-auto min-w-[300px]">
            <livewire:trip-summary
                :$itinerary
                :$model
                :is-completed="$model->data['status']['summary']['isCompleted']"
                :is-expanded="$model->data['status']['summary']['isExpanded']"
                :traveller-processed="$model->data['status']['traveller']['isCompleted']"
            />
        </div>
    </div>
    <!-- Footer with navigation buttons - takes 2 columns out of 3 on larger screens -->
    <div class="mt-10 mb-6 flex justify-between max-w-full md:max-w-[64.66%]">
        <a href="{{config('checkout.nezasa.base_url')}}/itineraries/{{$this->itineraryId}}">
            <button wire:click="goBack"
                    class="flex items-center gap-2 px-6 py-3 rounded-md border border-gray-300 dark:border-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{trans('checkout::page.trip_details.back')}}
            </button>
        </a>
        <button


        @class([
'inline-flex items-center gap-2 px-8 py-3 rounded-md text-white',
'bg-blue-500 hover:bg-blue-600' => ! $checkingAvailability,
'bg-gray-300 hover:bg-gray-500' => $checkingAvailability,
])
        @if($paymentPageUrl)
            <a href="{{ $paymentPageUrl }}" class="w-full h-full">
                @endif

                <span class="whitespace-nowrap">
                {{ trans('checkout::page.trip_details.pay') }}
                    {{ \Illuminate\Support\Number::currency($itinerary->price->downPayment->amount, $itinerary->price->downPayment->currency) }}
              </span>

                @if($checkingAvailability)
                    <span class="inline-flex w-4 h-4 items-center justify-center">
                <svg class="h-4 w-4 animate-spin /* toggle visibility yourself */"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
              </span>
                @endif

                @if($paymentPageUrl)
            </a>
            @endif
            </button>

    </div>
    <div class="text-center mb-10 text-gray-500 dark:text-gray-400 max-w-full md:max-w-[66.66%]">
        {{trans('checkout::page.footer.copyright', ['name' => 'Squad Ruby Tours'])}}
    </div>
</div>
