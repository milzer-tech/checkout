@use(Nezasa\Checkout\Enums\Section)
@use(Illuminate\Support\Facades\Config)
@use(Nezasa\Checkout\Facades\InsuranceFacade)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.travel_insurance')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{$isInsuranceAvailable ? '' : 'hidden'}}"
    onEdit="reopen('{{Section::Insurance->value}}')"

>
{{--    <div class="space-y-4">--}}

{{--        @if(InsuranceFacade::isAvailable() && $contact)--}}
{{--            @if(Config::boolean('checkout.insurance.vertical.active'))--}}
{{--                @push('scripts')--}}
{{--                    <script src="https://cdn.jsdelivr.net/npm/@vertical-insure/embedded-offer"></script>--}}
{{--                    <script>--}}
{{--                        const INSURANCE_READY_TIMEOUT_MS = 20000;--}}

{{--                        let currentInitId = 0;--}}
{{--                        let readyTimeoutId = null;--}}

{{--                        function showLoader() {--}}
{{--                            document.getElementById("insurance_loading").style.display = "flex";--}}
{{--                            document.getElementById("insurance_error").classList.add("hidden");--}}
{{--                        }--}}

{{--                        function hideLoader() {--}}
{{--                            document.getElementById("insurance_loading").style.display = "none";--}}
{{--                        }--}}

{{--                        function showError(msg = "Insurance offer couldn’t be loaded. Please try again.") {--}}
{{--                            hideLoader();--}}
{{--                            document.getElementById("insurance_error_text").textContent = msg;--}}
{{--                            document.getElementById("insurance_error").classList.remove("hidden");--}}
{{--                        }--}}

{{--                        async function initVerticalInsure(config) {--}}
{{--                            window.__lastInsuranceConfig = config;--}}

{{--                            const myInitId = ++currentInitId;--}}
{{--                            showLoader();--}}

{{--                            clearTimeout(readyTimeoutId);--}}
{{--                            readyTimeoutId = setTimeout(() => {--}}
{{--                                // only affect the latest init--}}
{{--                                if (myInitId !== currentInitId) return;--}}
{{--                                showError("Insurance offer is taking too long to load. Please try again.");--}}
{{--                            }, INSURANCE_READY_TIMEOUT_MS);--}}

{{--                            // IMPORTANT: don't clear the offer container here, or the user will see it disappear--}}
{{--                            // document.getElementById("insurance_offer").innerHTML = "";--}}

{{--                            try {--}}
{{--                                if (window.verticalInsureInstance && typeof window.verticalInsureInstance.destroy === "function") {--}}
{{--                                    try {--}}
{{--                                        window.verticalInsureInstance.destroy();--}}
{{--                                    } catch (_) {--}}
{{--                                    }--}}
{{--                                }--}}
{{--                                window.verticalInsureInstance = new VerticalInsure("#insurance_offer", config);--}}
{{--                            } catch (e) {--}}
{{--                                if (myInitId !== currentInitId) return;--}}
{{--                                clearTimeout(readyTimeoutId);--}}
{{--                                console.error(e);--}}
{{--                                showError("Insurance offer couldn’t be initialized.");--}}
{{--                            }--}}
{{--                        }--}}

{{--                        // Hide loader only for the latest init cycle--}}
{{--                        window.addEventListener("offer-ready", (e) => {--}}
{{--                            // Ignore stale ready events from previous instances--}}
{{--                            // (If Vertical fires ready twice or older instance emits late)--}}
{{--                            if (currentInitId === 0) return;--}}

{{--                            clearTimeout(readyTimeoutId);--}}

{{--                            if (e?.detail?.offersAvailable === false) {--}}
{{--                                showError("The insurance offers are only available in US and CAN. If you need to add an insurance, please change the contact's address to US or CAN.");--}}
{{--                                return;--}}
{{--                            }--}}

{{--                            hideLoader();--}}
{{--                        });--}}

{{--                        window.addEventListener("offer-state-change", (e) => {--}}
{{--                            @this.--}}
{{--                            call('handleInsuranceQuote', e.detail.quotes[0]);--}}
{{--                        });--}}

{{--                        window.retryInsuranceOffer = () => {--}}
{{--                            if (window.__lastInsuranceConfig) initVerticalInsure(window.__lastInsuranceConfig);--}}
{{--                        };--}}

{{--                        document.addEventListener("DOMContentLoaded", () => {--}}
{{--                            initVerticalInsure(@js($this->verticalInsuranceConfig));--}}
{{--                        });--}}

{{--                        window.addEventListener("insurance-config-updated", (e) => {--}}
{{--                            initVerticalInsure(e.detail.config);--}}
{{--                        });--}}

{{--                        document.addEventListener('livewire:init', () => {--}}
{{--                            initVerticalInsure(@js($this->verticalInsuranceConfig));--}}
{{--                        });--}}
{{--                    </script>--}}
{{--                @endpush--}}


{{--                <div wire:ignore class="relative">--}}
{{--                    --}}{{-- Offer --}}
{{--                    <div id="insurance_offer"></div>--}}

{{--                    --}}{{-- Loader overlay (default visible) --}}
{{--                    <div id="insurance_loading"--}}
{{--                         class="absolute inset-0 z-10 flex items-center justify-center gap-2 bg-white/70 text-gray-600"--}}
{{--                         style="display:flex;">--}}

{{--                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"--}}
{{--                             viewBox="0 0 24 24">--}}
{{--                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>--}}
{{--                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>--}}
{{--                        </svg>--}}
{{--                        <span>Loading insurance...</span>--}}
{{--                    </div>--}}

{{--                    --}}{{-- Error overlay --}}
{{--                    <div id="insurance_error"--}}
{{--                         class="hidden mb-6 px-4">--}}
{{--                        <div class="rounded-md border border-red-200 bg-red-50 text-red-700 px-6 py-4 text-sm">--}}
{{--                            <div class="flex items-center justify-between gap-4">--}}
{{--            <span id="insurance_error_text">--}}
{{--                No insurance offers available right now.--}}
{{--            </span>--}}

{{--                                <button type="button"--}}
{{--                                        class="underline whitespace-nowrap"--}}
{{--                                        onclick="window.retryInsuranceOffer?.()">--}}
{{--                                    Retry--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}


{{--                </div>--}}

{{--            @endif--}}
{{--        @endif--}}

{{--        <div class="space-y-4 mt-8">--}}
{{--            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>--}}

{{--            <div class="flex justify-between items-center">--}}
{{--                <div></div>--}}
{{--                <button type="button" wire:click="next"--}}
{{--                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">--}}
{{--                    Next--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}










{{--    <div class="space-y-4">--}}
{{--        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-start">--}}

{{--            @foreach($offers as $offer)--}}
{{--            <label class="border border-gray-200 rounded-xl p-4 cursor-pointer hover:shadow-sm">--}}
{{--                <div class="flex items-start gap-3">--}}
{{--                    <!-- RADIO -->--}}
{{--                    <input type="radio"--}}
{{--                           class="h-4 w-4 mt-1 text-blue-600"--}}
{{--                           name="INSURANCE_GROUP"--}}
{{--                           value="VALUE_TRAVEL_PLUS"--}}
{{--                           checked>--}}

{{--                    <div class="flex-1">--}}
{{--                        <div class="flex items-start justify-between gap-3">--}}
{{--                            <div class="text-gray-900 font-medium">--}}
{{--                               {{$offer->title}}--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="mt-1 text-sm">--}}
{{--                            <span class="text-emerald-600 font-medium">+ {{$offer->price->toHtml()}}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <!-- FEATURES -->--}}
{{--                <ul class="mt-4 space-y-2 text-sm text-gray-600">--}}
{{--                    @foreach($offer->coverage as $coverage)--}}
{{--                    <li class="flex items-start gap-2">--}}
{{--                        <span class="mt-0.5 text-gray-500">✓</span>--}}
{{--                        <span>{{$coverage}}</span>--}}
{{--                    </li>--}}
{{--                    @endforeach--}}
{{--                </ul>--}}
{{--            </label>--}}
{{--            @endforeach--}}

{{--            <!-- Card 3: No insurance (example not selected) -->--}}
{{--            <label class="border border-gray-200 rounded-xl p-4 cursor-pointer hover:shadow-sm">--}}
{{--                <div class="flex items-start gap-3">--}}
{{--                    <input type="radio"--}}
{{--                           class="h-4 w-4 mt-1 text-blue-600"--}}
{{--                           name="INSURANCE_GROUP"--}}
{{--                           value="VALUE_NO_INSURANCE">--}}

{{--                    <div class="flex-1">--}}
{{--                        <div class="flex items-start justify-between gap-3">--}}
{{--                            <div class="text-gray-900 font-medium">--}}
{{--                                No insurance--}}
{{--                            </div>--}}

{{--                            <!-- small icon (optional) -->--}}
{{--                            <span class="shrink-0 text-gray-400" aria-hidden="true">⛨</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </label>--}}

{{--        </div>--}}
{{--    </div>--}}




    <div class="relative" x-data @insurance-load-offers.window="$wire.call('loadOffer')">
        <div wire:loading.flex wire:target="listen,loadOffer"
             class="absolute inset-0 z-10 items-center justify-center gap-3 rounded-xl bg-white/80 backdrop-blur-sm text-gray-700">
            <svg class="h-5 w-5 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <span class="text-sm">Loading insurance offers...</span>
        </div>

        <div wire:loading.remove wire:target="listen,loadOffer" class="space-y-4">
            <div class="grid grid-cols-1 gap-4">

            @foreach($offers as $offer)
                <label class="border rounded-xl p-4 cursor-pointer hover:shadow-sm w-full block
                 {{ $selectedOfferId == $offer->id
        ? 'border-blue-500 ring-1 ring-blue-500'
        : 'border-gray-200' }}
                ">
                    <div class="flex items-start gap-3">
                        <input type="radio"
                               class="h-4 w-4 mt-1 text-blue-600 shrink-0"
                               name="INSURANCE_GROUP"
                               value="{{ $offer->id ?? $loop->index }}"
                               wire:click="updateSelectedOfferId('{{$offer->id}}')"
                        >

                        <div class="min-w-0 flex-1">
                            <div class="text-gray-900 font-medium break-words whitespace-normal leading-snug">
                                {{ $offer->title }}
                            </div>

                            <div class="mt-1 text-sm">
                                <span class="text-emerald-600 font-medium">+ {!! $offer->price->toHtml() !!}</span>
                            </div>
                        </div>
                    </div>

                    @if(!empty($offer->coverage))
                        <ul class="mt-4 space-y-2 text-sm text-gray-600">
                            @foreach($offer->coverage as $coverage)
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 text-gray-500 shrink-0">✓</span>
                                    <span class="break-words whitespace-normal">{{ $coverage }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </label>
            @endforeach

            @if(! empty($offers))


            <!-- No insurance -->
            <label class="border border-gray-200 rounded-xl p-4 cursor-pointer hover:shadow-sm w-full block">
                <div class="flex items-start gap-3">
                    <input type="radio"
                           class="h-4 w-4 mt-1 text-blue-600 shrink-0"
                           name="INSURANCE_GROUP"
                           value="VALUE_NO_INSURANCE"
                           wire:click="updateSelectedOfferId(null)"
                    >

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="text-gray-900 font-medium break-words whitespace-normal leading-snug">
                                No insurance
                            </div>
                            <span class="shrink-0 text-gray-400" aria-hidden="true">⛨</span>
                        </div>
                    </div>
                </div>
            </label>
                @endif

        </div>


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
    </div>

</x-checkout::editable-box>
