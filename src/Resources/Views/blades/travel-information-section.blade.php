@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')
@php($sections = [
    'health' => trans('checkout::page.trip_details.travel_information_health'),
    'entry' => trans('checkout::page.trip_details.travel_information_entry_requirements'),
    'visa' => trans('checkout::page.trip_details.travel_information_visa_requirements'),
    'transit_visa' => trans('checkout::page.trip_details.travel_information_transit_visa_requirements'),
])

<x-checkout::editable-box
    title="{{ trans('checkout::page.trip_details.general_entry_requirements') }}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{ $this->shouldRender() ? '' : 'hidden' }}"
    onEdit="reopen('{{ Section::TravelInformation->value }}')"
>
    <div class="space-y-6">
        <p class="text-sm leading-relaxed text-gray-600">
            {{ trans('checkout::page.trip_details.travel_information_intro') }}
        </p>

        <div class="divide-y divide-gray-200 border-y border-gray-200">
            @foreach($sections as $key => $label)
                <details class="group py-1" @if($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between py-4 text-sm font-medium text-gray-900">
                        <span>{{ $label }}</span>
                        <svg
                            class="h-5 w-5 text-gray-600 transition-transform group-open:rotate-180"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 011.06 0L10 10.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 8.28a.75.75 0 010-1.06z" clip-rule="evenodd"/>
                        </svg>
                    </summary>

                    <div class="space-y-4 pb-5">
                        @forelse($combinations as $combination)
                            <div class="rounded-md bg-gray-50 p-4">
                                <h4 class="mb-3 text-sm font-semibold text-gray-900">{{ $combination['title'] }}</h4>

                                <div class="whitespace-pre-line text-sm leading-relaxed text-gray-700">
                                    @if(filled($combination[$key] ?? null))
                                        {{ str_replace(["\r\n", "\r"], "\n", $combination[$key]) }}
                                    @else
                                        {{ trans('checkout::page.trip_details.travel_information_data_not_found') }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                                {{ trans('checkout::page.trip_details.travel_information_data_not_found') }}
                            </div>
                        @endforelse
                    </div>
                </details>
            @endforeach
        </div>

        <div class="rounded-md p-6 mt-4 border
            @error('travelInformationConfirmed')
            border-red-500 @else border-gray-300 @enderror">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input
                    type="checkbox"
                    wire:model="travelInformationConfirmed"
                    wire:change="toggleTravelInformationConfirmation($event.target.checked)"
                    class="h-5 w-5 text-blue-600 border-gray-300 rounded">

                <span class="text-sm inline text-gray-600">
                    {{ trans('checkout::page.trip_details.travel_information_confirmation') }}
                </span>
            </label>

            @error('travelInformationConfirmed')
            <p class="text-red-600 text-sm mt-2">{{ trans('checkout::input.validations.agree_to_continue') }}</p>
            @enderror
        </div>

        <div class="space-y-4 mt-8">
            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

            <div class="flex justify-between items-center">
                <div></div>
                <button type="button"
                        wire:click="next"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    {{ trans('checkout::page.trip_details.next') }}
                </button>
            </div>
        </div>
    </div>
</x-checkout::editable-box>
