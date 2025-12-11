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

                    <script>
                        function showInsuranceLoader() {
                            document.getElementById("insurance_loading").style.display = "flex";
                            document.getElementById("insurance_offer").style.display = "none";
                        }

                        function hideInsuranceLoader() {
                            document.getElementById("insurance_loading").style.display = "none";
                            document.getElementById("insurance_offer").style.display = "block";
                        }

                        async function initVerticalInsure(config) {
                            showInsuranceLoader();

                            // Optional: destroy previous instance if supported
                            if (window.verticalInsureInstance && typeof window.verticalInsureInstance.destroy === 'function') {
                                window.verticalInsureInstance.destroy();
                            }

                            // Slight delay so loader is visually noticeable
                            await new Promise(r => setTimeout(r, 150));

                            window.verticalInsureInstance = new VerticalInsure("#insurance_offer", config);

                            // Hide loader after widget finishes rendering
                            // The library does not emit a dedicated "ready" event,
                            // so we use a small timeout to ensure the UI is injected.
                            setTimeout(() => {
                                hideInsuranceLoader();
                            }, 300);
                        }

                        document.addEventListener('DOMContentLoaded', () => {
                            initVerticalInsure(@js($this->verticalInsuranceConfig));
                        });

                        window.addEventListener('insurance-config-updated', (e) => {
                            initVerticalInsure(e.detail.config);
                        });

                        window.addEventListener("offer-state-change", (e) => {
                            @this.call('handleInsuranceQuote', e.detail.quotes[0]);
                        });
                    </script>

                @endpush


                    <div id="insurance_loading" class="flex items-center gap-2 text-gray-600" style="display: none;">
                        <svg class="h-4 w-4 animate-spin"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25"
                                    cx="12" cy="12" r="10"
                                    stroke="currentColor"
                                    stroke-width="4" />
                            <path class="opacity-75"
                                  fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span>Loading insurance...</span>
                    </div>

                    <div id="insurance_offer" wire:ignore wire:key="insurance_offer"></div>

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
