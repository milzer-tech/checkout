@php use Nezasa\Checkout\Enums\Section; @endphp
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
                    :is-completed="$model->isCompleted(Section::Contact)"
                    :is-expanded="$model->isExpanded(Section::Contact)"
                />
                <livewire:traveler-details
                    :$allocatedPax
                    :$passengerRequirements
                    :$countryCodes
                    :$countriesResponse
                    :$model
                    :$itinerary
                    :is-completed="$model->isCompleted(Section::Traveller)"
                    :is-expanded="$model->isExpanded(Section::Traveller)"
                />

                <livewire:activity-section
                    :shouldRender="$itinerary->activities->isNotEmpty()"
                    :$model
                    :is-completed="$model->isCompleted(Section::Activity)"
                    :is-expanded="$model->isExpanded(Section::Activity)"
                />

                <livewire:promo-code-section
                    :$prices
                    :$model
                    :is-completed="$model->isCompleted(Section::Promo)"
                    :is-expanded="$model->isExpanded(Section::Promo)"
                />
                <livewire:additional-services-section
                    :$upsellItemsResponse
                    :$addedUpsellItems
                    :$model
                    :is-completed="$model->isCompleted(Section::AdditionalService)"
                    :is-expanded="$model->isExpanded(Section::AdditionalService)"
                />
                <livewire:insurance-section
                    :$itinerary
                    :$model
                    :is-completed="$model->data['status']['insurance']['isCompleted']"
                    :is-expanded="$model->data['status']['insurance']['isExpanded']"
                />

                <livewire:terms-section
                    :termsAndConditions="$itinerary->termsAndConditions"
                    :$model
                    :is-completed="$model->isCompleted(Section::TermsAndConditions)"
                    :is-expanded="$model->isExpanded(Section::TermsAndConditions)"
                />
                <livewire:payment-options-section
                    :$model
                    :is-completed="$model->isCompleted(Section::PaymentOptions)"
                    :is-expanded="$model->isExpanded(Section::PaymentOptions)"
                />
                <div class="mt-6"></div>
            </section>
        </div>
        <div class="overflow-auto min-w-[300px]">
            <livewire:trip-summary
                :$itinerary
                :$model
                :is-completed="$model->isCompleted(Section::Summary)"
                :is-expanded="$model->isExpanded(Section::Summary)"
                :traveller-processed="$model->isCompleted(Section::Traveller)"
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
        <div x-data="{ clicked: false }">

            <a
                href="{{ $paymentPageUrl }}"
                x-on:click="
            if (clicked || {{ $checkingAvailability ? 'true' : 'false' }}) {
                $event.preventDefault()
            } else {
                clicked = true
            }
        "
                x-bind:class="{
            'pointer-events-none cursor-not-allowed opacity-80':
                clicked || {{ $checkingAvailability ? 'true' : 'false' }}
        }"

                class="block w-max"
                style="text-decoration: none;"
            >

                <div
                    x-bind:disabled="clicked || {{ $checkingAvailability ? 'true' : 'false' }}"
                    @class([
                        'inline-flex items-center gap-2 px-8 py-3 rounded-md text-white',
                        'bg-blue-500 hover:bg-blue-600' => ! $checkingAvailability,
                        'bg-gray-300 hover:bg-gray-500 pointer-events-none' => $checkingAvailability,
                    ])
                >

                    {{-- Text always visible --}}
                    <span class="whitespace-nowrap">
                {{ trans('checkout::page.trip_details.pay') }}
                        {{ \Illuminate\Support\Number::currency($itinerary->price->downPayment->amount, $itinerary->price->downPayment->currency) }}
            </span>

                    {{-- CASE 1: availability loading --}}
                    @if($checkingAvailability)
                        <span class="inline-flex w-4 h-4 items-center justify-center">
                    <svg class="h-4 w-4 animate-spin"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25"
                                cx="12" cy="12" r="10"
                                stroke="currentColor"
                                stroke-width="4"/>
                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                </span>
                    @else
                        {{-- CASE 2: after click --}}
                        <span class="inline-flex w-4 h-4 items-center justify-center"
                              x-show="clicked"
                              x-cloak>
                    <svg class="h-4 w-4 animate-spin"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25"
                                cx="12" cy="12" r="10"
                                stroke="currentColor"
                                stroke-width="4"/>
                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                </span>
                    @endif

                </div>

            </a>

        </div>


    </div>
    <div class="text-center mb-10 text-gray-500 dark:text-gray-400 max-w-full md:max-w-[66.66%]">
        {{trans('checkout::page.footer.copyright', ['name' => 'Squad Ruby Tours'])}}
    </div>

    {{-- Global toast (top-right) --}}
    <div
        x-data="{
        show: false,
        message: '',
        title: '',
        type: 'info',
        hideTimeout: null,
        open(detail) {
            this.message = detail.message ?? '';
            this.title = detail.title ?? '';
            this.type = detail.type ?? 'info';
            this.show = true;

            if (this.hideTimeout) clearTimeout(this.hideTimeout);
            this.hideTimeout = setTimeout(() => this.show = false, 5000);
        }
    }"
        x-on:toast.window="open($event.detail[0])"
        class="fixed top-10 right-10 z-[999]"
    >
        <div
            x-show="show"
            x-transition.opacity.duration.300ms
            x-transition.scale.origin.top.right.duration.200ms
            x-cloak
            class="w-[420px] bg-white rounded-2xl border border-slate-200 shadow-[0_20px_55px_rgba(20,30,70,0.12)] p-5 flex gap-4 items-start"
        >
            {{-- Icon --}}
            <div
                class="h-4 w-4 rounded-full mt-1.5"
                :class="{
                'bg-red-600': type === 'error',
                'bg-green-600': type === 'success',
                'bg-blue-600': type === 'info'
            }"
            ></div>

            {{-- Content --}}
            <div class="flex-1">
                <div class="text-base font-semibold text-slate-900 mb-1"
                     x-text="title">
                </div>

                <div class="text-sm text-slate-600 leading-relaxed"
                     x-text="message">
                </div>
            </div>

            {{-- Close --}}
            <button
                @click="show = false"
                class="text-slate-400 hover:text-slate-600 transition"
            >
                ✕
            </button>
        </div>
    </div>

</div>
