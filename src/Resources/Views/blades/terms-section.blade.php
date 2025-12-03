@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.important_information')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{$termsAndConditions->sections->isEmpty() ? 'hidden' : ''}}"
    onEdit="expand('{{Section::TermsAndConditions->value}}')"
>

    @foreach($termsAndConditions->sections as $index => $term)

        <div class="space-y-3 mb-10">
            <!-- Title -->
            <h3 class="text-lg font-semibold text-gray-900">{{ $term->header }}</h3>

            <!-- Text -->
            <div class="text-sm text-gray-600 leading-relaxed">
                {!! $term->text !!}
            </div>

            @if($term->checkboxText)
                <!-- Checkbox Block Container -->
                <div class="rounded-md p-6 mt-4 border
                @error('acceptedTerms.' .$term->getKey())
                border-red-500 @else border-gray-300 @enderror">

                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="acceptedTerms.{{ $term->getKey() }}"
                            wire:change="toggleBox('acceptedTerms.{{ $term->getKey() }}', $event.target.checked)"
                            class="h-5 w-5 text-blue-600 border-gray-300 rounded">

                        <span class="text-sm text-gray-600">
                        I agree to the Terms and Conditions stated above.
                        </span>
                    </label>

                    @error('acceptedTerms.' .$term->getKey())
                    <p class="text-red-600 text-sm mt-2">{{trans('checkout::input.validations.agree_to_continue')}}</p>
                    @enderror
                </div>
            @endif
        </div>
    @endforeach

    <div class="space-y-4 mt-8">
        <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

        <div class="flex justify-between items-center">
            <div></div>
            <button type="button"
                    wire:click="next"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                {{trans('checkout::page.trip_details.next')}}
            </button>
        </div>
    </div>

</x-checkout::editable-box>
