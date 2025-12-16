@use(Nezasa\Checkout\Enums\Section)
@use(Illuminate\Support\Facades\Config)
@use(Nezasa\Checkout\Facades\InsuranceFacade)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.travel_insurance')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{InsuranceFacade::isAvailable() ? '' : 'hidden'}}"
    onEdit="expand('{{Section::Insurance->value}}')"

>
    <div class="space-y-4">

        @if(InsuranceFacade::isAvailable() && $contact)
            @if(Config::boolean('checkout.insurance.vertical.active'))
                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/@vertical-insure/embedded-offer"></script>
                    <script>
                        const INSURANCE_READY_TIMEOUT_MS = 20000;

                        let currentInitId = 0;
                        let readyTimeoutId = null;

                        function showLoader() {
                            document.getElementById("insurance_loading").style.display = "flex";
                            document.getElementById("insurance_error").classList.add("hidden");
                        }

                        function hideLoader() {
                            document.getElementById("insurance_loading").style.display = "none";
                        }

                        function showError(msg = "Insurance offer couldn’t be loaded. Please try again.") {
                            hideLoader();
                            document.getElementById("insurance_error_text").textContent = msg;
                            document.getElementById("insurance_error").classList.remove("hidden");
                        }

                        async function initVerticalInsure(config) {
                            window.__lastInsuranceConfig = config;

                            const myInitId = ++currentInitId;
                            showLoader();

                            clearTimeout(readyTimeoutId);
                            readyTimeoutId = setTimeout(() => {
                                // only affect the latest init
                                if (myInitId !== currentInitId) return;
                                showError("Insurance offer is taking too long to load. Please try again.");
                            }, INSURANCE_READY_TIMEOUT_MS);

                            // IMPORTANT: don't clear the offer container here, or the user will see it disappear
                            // document.getElementById("insurance_offer").innerHTML = "";

                            try {
                                if (window.verticalInsureInstance && typeof window.verticalInsureInstance.destroy === "function") {
                                    try {
                                        window.verticalInsureInstance.destroy();
                                    } catch (_) {
                                    }
                                }
                                window.verticalInsureInstance = new VerticalInsure("#insurance_offer", config);
                            } catch (e) {
                                if (myInitId !== currentInitId) return;
                                clearTimeout(readyTimeoutId);
                                console.error(e);
                                showError("Insurance offer couldn’t be initialized.");
                            }
                        }

                        // Hide loader only for the latest init cycle
                        window.addEventListener("offer-ready", (e) => {
                            // Ignore stale ready events from previous instances
                            // (If Vertical fires ready twice or older instance emits late)
                            if (currentInitId === 0) return;

                            clearTimeout(readyTimeoutId);

                            if (e?.detail?.offersAvailable === false) {
                                showError("The insurance offers are not available in your region.");
                                return;
                            }

                            hideLoader();
                        });

                        window.addEventListener("offer-state-change", (e) => {
                            @this.
                            call('handleInsuranceQuote', e.detail.quotes[0]);
                        });

                        window.retryInsuranceOffer = () => {
                            if (window.__lastInsuranceConfig) initVerticalInsure(window.__lastInsuranceConfig);
                        };

                        document.addEventListener("DOMContentLoaded", () => {
                            initVerticalInsure(@js($this->verticalInsuranceConfig));
                        });

                        window.addEventListener("insurance-config-updated", (e) => {
                            initVerticalInsure(e.detail.config);
                        });

                        document.addEventListener('livewire:init', () => {
                            initVerticalInsure(@js($this->verticalInsuranceConfig));
                        });
                    </script>
                @endpush


                <div wire:ignore class="relative">
                    {{-- Offer --}}
                    <div id="insurance_offer"></div>

                    {{-- Loader overlay (default visible) --}}
                    <div id="insurance_loading"
                         class="absolute inset-0 z-10 flex items-center justify-center gap-2 bg-white/70 text-gray-600"
                         style="display:flex;">

                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span>Loading insurance...</span>
                    </div>

                    {{-- Error overlay --}}
                    <div id="insurance_error"
                         class="hidden mb-6 px-4">
                        <div class="rounded-md border border-red-200 bg-red-50 text-red-700 px-6 py-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
            <span id="insurance_error_text">
                No insurance offers available right now.
            </span>

                                <button type="button"
                                        class="underline whitespace-nowrap"
                                        onclick="window.retryInsuranceOffer?.()">
                                    Retry
                                </button>
                            </div>
                        </div>
                    </div>


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
