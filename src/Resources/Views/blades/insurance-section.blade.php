@use(Nezasa\Checkout\Enums\Section)
@use(Illuminate\Support\Facades\Config)
@use(Nezasa\Checkout\Facades\InsuranceFacade)
@php
    $state = $isExpanded ? 'editing' : 'valid';
    $insuranceVerticalScriptI18n = [
        'offerLoadError' => trans('checkout::page.trip_details.insurance_offer_load_error'),
        'offerTimeout' => trans('checkout::page.trip_details.insurance_offer_timeout'),
        'offerInitError' => trans('checkout::page.trip_details.insurance_offer_init_error'),
        'offersRegionNotice' => trans('checkout::page.trip_details.insurance_offers_region_notice'),
    ];
@endphp

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.travel_insurance')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{$isInsuranceAvailable ? '' : 'hidden'}}"
    onEdit="reopen('{{Section::Insurance->value}}')"

>

    @if(config()->boolean('checkout.insurance.vertical.active'))
    <div class="space-y-4">
        @if($shouldInitVerticalWidget)
            <div wire:init="initVerticalWidget"></div>
        @endif

        @if($isInsuranceAvailable && $contact)
            @if(Config::boolean('checkout.insurance.vertical.active'))
                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/@vertical-insure/embedded-offer"></script>
                    <script>
                        const insuranceUiI18n = @json($insuranceVerticalScriptI18n);
                        const INSURANCE_READY_TIMEOUT_MS = 20000;

                        let currentInitId = 0;
                        let readyTimeoutId = null;

                        function logVertical(direction, label, payload) {
                            const prefix = '[Vertical Insurance]';
                            if (payload !== undefined) {
                                console.log(prefix, direction, label, payload);
                            } else {
                                console.log(prefix, direction, label);
                            }
                        }

                        function showLoader() {
                            document.getElementById("insurance_loading").style.display = "flex";
                            document.getElementById("insurance_error").classList.add("hidden");
                        }

                        function hideLoader() {
                            document.getElementById("insurance_loading").style.display = "none";
                        }

                        function showError(msg = insuranceUiI18n.offerLoadError) {
                            logVertical('← Vertical', 'UI / error message', msg);
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
                                showError(insuranceUiI18n.offerTimeout);
                            }, INSURANCE_READY_TIMEOUT_MS);

                            // IMPORTANT: don't clear the offer container here, or the user will see it disappear
                            // document.getElementById("insurance_offer").innerHTML = "";

                            try {
                                logVertical('→ Vertical', 'init config (embedded-offer)', config);
                                if (window.verticalInsureInstance && typeof window.verticalInsureInstance.destroy === "function") {
                                    try {
                                        window.verticalInsureInstance.destroy();
                                    } catch (_) {
                                    }
                                }
                                window.verticalInsureInstance = new VerticalInsure("#insurance_offer", config, {
                                    onError(error) {
                                        logVertical('← Vertical', 'SDK onError', error);
                                    },
                                });
                            } catch (e) {
                                if (myInitId !== currentInitId) return;
                                clearTimeout(readyTimeoutId);
                                logVertical('← Vertical', 'init exception', e);
                                console.error(e);
                                showError(insuranceUiI18n.offerInitError);
                            }
                        }

                        // Hide loader only for the latest init cycle
                        window.addEventListener("offer-ready", (e) => {
                            logVertical('← Vertical', 'offer-ready (event)', e?.detail);
                            // Ignore stale ready events from previous instances
                            // (If Vertical fires ready twice or older instance emits late)
                            if (currentInitId === 0) return;

                            clearTimeout(readyTimeoutId);

                            if (e?.detail?.offersAvailable === false) {
                                showError(insuranceUiI18n.offersRegionNotice);
                                return;
                            }

                            hideLoader();
                        });

                        window.addEventListener("offer-state-change", (e) => {
                            logVertical('← Vertical', 'offer-state-change (event)', e?.detail);
                            @this.
                            call('handleInsuranceQuote', e.detail.quotes[0]);
                        });

                        window.retryInsuranceOffer = () => {
                            if (window.__lastInsuranceConfig) initVerticalInsure(window.__lastInsuranceConfig);
                        };

                        window.addEventListener("insurance-reset-ui", () => {
                            try {
                                if (window.verticalInsureInstance && typeof window.verticalInsureInstance.destroy === "function") {
                                    window.verticalInsureInstance.destroy();
                                }
                            } catch (_) {}

                            const offerEl = document.getElementById("insurance_offer");
                            if (offerEl) offerEl.innerHTML = "";

                            showLoader();
                        });

                        window.addEventListener("insurance-config-updated", (e) => {
                            logVertical('→ Vertical', 'Livewire insurance-config-updated', e?.detail?.config);
                            initVerticalInsure(e.detail.config);
                        });

                    </script>
                @endpush


                <div wire:ignore class="relative">

                    <div id="insurance_offer"></div>

                    <div id="insurance_loading"
                         class="absolute inset-0 z-10 flex items-center justify-center gap-2 bg-white/70 text-gray-600"
                         style="display:flex;">

                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span>{{ trans('checkout::page.trip_details.insurance_loading_widget') }}</span>
                    </div>


                    <div id="insurance_error"
                         class="hidden mb-6 px-4">
                        <div class="rounded-md border border-red-200 bg-red-50 text-red-700 px-6 py-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
            <span id="insurance_error_text">
                {{ trans('checkout::page.trip_details.insurance_no_offers_default') }}
            </span>

                                <button type="button"
                                        class="underline whitespace-nowrap"
                                        onclick="window.retryInsuranceOffer?.()">
                                    {{ trans('checkout::page.trip_details.insurance_retry') }}
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
                    {{ trans('checkout::page.trip_details.next') }}
                </button>
            </div>
        </div>

    </div>



    @else

    <div class="relative" x-data @insurance-load-offers.window="$wire.call('loadOffer')">
        <div wire:loading.flex wire:target="listen,loadOffer"
             class="absolute inset-0 z-10 items-center justify-center gap-3 rounded-xl bg-white/80 backdrop-blur-sm text-gray-700">
            <svg class="h-5 w-5 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <span class="text-sm">{{ trans('checkout::page.trip_details.insurance_loading_offers') }}</span>
        </div>

        <div wire:loading.remove wire:target="listen,loadOffer" class="space-y-4">
            <div class="grid grid-cols-1 gap-4">

            @if($insuranceProviderIsAvailable)
            @foreach($offers as $offer)
                <label class="relative border rounded-xl p-4 cursor-pointer hover:shadow-sm w-full block
                 {{ $selectedOfferId == $offer->id
        ? 'border-blue-500 ring-1 ring-blue-500'
        : 'border-gray-200' }}
                ">
                    @if($insuranceProviderLogo)
                        <img
                            src="{{ $insuranceProviderLogo }}"
                            alt="{{ $insuranceProviderName ?? trans('checkout::page.trip_details.travel_insurance') }}"
                            class="absolute right-4 top-4 max-h-9 w-28 object-contain object-right"
                            loading="lazy"
                        />
                    @endif

                    <div class="flex items-start gap-3">
                        <input type="radio"
                               class="h-4 w-4 mt-1 text-blue-600 shrink-0"
                               name="INSURANCE_GROUP"
                               value="{{ $offer->id ?? $loop->index }}"
                               wire:click="updateSelectedOfferId('{{$offer->id}}')"
                        >

                        <div class="min-w-0 flex-1">
                            <div class="min-w-0 pr-24">
                                <div class="text-gray-900 font-medium break-words whitespace-normal leading-snug">
                                    {{ $offer->title }}
                                </div>

                                <div class="mt-1 text-sm">
                                    <span class="text-emerald-600 font-medium">+ {!! $offer->price->toHtml() !!}</span>
                                </div>
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

                    @if(!empty($offer->documentLinks))
                        <ul class="mt-3 space-y-1 text-sm">
                            @foreach($offer->documentLinks as $documentLink)
                                @php
                                    $documentLabel = data_get($documentLink, 'label');
                                    $documentUrl = data_get($documentLink, 'url');
                                @endphp

                                @if(is_string($documentLabel) && $documentLabel !== '' && is_string($documentUrl) && $documentUrl !== '')
                                    <li>
                                        <a
                                            href="{{ $documentUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-blue-600 underline hover:text-blue-700"
                                            onclick="event.stopPropagation()"
                                        >
                                            {{ $documentLabel }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif

                    @if($requiresInsurancePaymentData && $selectedOfferId === $offer->id)
                        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4" wire:click.stop>
                            @php
                                $firstPaymentField = $insurancePaymentFields[0] ?? [];
                                $offerIndex = $loop->index;
                            @endphp

                            <div class="text-sm font-medium text-gray-900">
                                {{ $firstPaymentField['sectionTitle'] ?? trans('checkout::page.trip_details.insurance_iban_section_title') }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
                                {{ $firstPaymentField['sectionIntro'] ?? trans('checkout::page.trip_details.insurance_iban_section_intro') }}
                            </div>

                            @foreach($insurancePaymentFields as $paymentField)
                                @php
                                    $fieldKey = $paymentField['key'] ?? null;
                                    $fieldType = $paymentField['type'] ?? 'text';
                                    $inputType = $fieldType === 'card_cvc' ? 'password' : 'text';
                                @endphp

                                @if(is_string($fieldKey) && $fieldKey !== '')
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700" for="insurance-payment-{{ $fieldKey }}-{{ $offerIndex }}">
                                            {{ $paymentField['label'] ?? $fieldKey }}
                                        </label>
                                        <input
                                            id="insurance-payment-{{ $fieldKey }}-{{ $offerIndex }}"
                                            type="{{ $inputType }}"
                                            inputmode="{{ $paymentField['inputMode'] ?? 'text' }}"
                                            autocomplete="{{ $paymentField['autocomplete'] ?? 'off' }}"
                                            placeholder="{{ $paymentField['placeholder'] ?? '' }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            wire:model.live.debounce.400ms="insurancePaymentData.{{ $fieldKey }}"
                                        />

                                        @error("insurancePaymentData.$fieldKey")
                                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach
                        </div>
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
                                {{ $insuranceNoSelectionText }}
                            </div>
                            <span class="shrink-0 text-gray-400" aria-hidden="true">⛨</span>
                        </div>
                    </div>
                </div>
            </label>
                @endif
                @else
                    <div class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <svg class="h-5 w-5 text-red-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M4.93 19.07A10 10 0 1 1 19.07 4.93 10 10 0 0 1 4.93 19.07Z" />
                        </svg>
                        <div class="min-w-0">{{ $notAvailableMessage }}</div>
                    </div>
                @endif

        </div>

        <div class="space-y-4 mt-8">
                        <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>
                        <div class="flex justify-between items-center">
                            <div></div>
                            <button type="button" wire:click="next"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                                {{ trans('checkout::page.trip_details.next') }}
                            </button>
                        </div>
                    </div>
        </div>
    </div>

    @endif

</x-checkout::editable-box>
