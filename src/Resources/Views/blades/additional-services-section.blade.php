@use(Illuminate\Support\Number)
@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.additional_services')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{$upsellItemsResponse->offers->isEmpty() ? 'hidden' : ''}}"
    onEdit="expand('{{Section::AdditionalService->value}}')"
>


    <div class="space-y-10">
    <form>
        @foreach($upsellItemsResponse->offers as $offer)

            <div class="space-y-4 mb-14">

                <h3 class="text-l font-semibold">{{$offer->name}}</h3>
                <!-- TOP: TEXT + IMAGE (50/50) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <!-- Left text -->
                    <div class="text-gray-700 leading-relaxed">
                        {!! $offer->description !!}
                    </div>

                    <!-- Right image -->
                    <div>
                        @if($offer->pictures->isNotEmpty())
                            <img src="{{$offer->pictures->first()->url}}"
                                 class="w-full h-28 md:h-32 lg:h-36 object-cover rounded-xl">
                        @endif
                    </div>

                </div>


                <!-- OPTIONS GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($offer->serviceCategories as $index => $category)

                        <label class="
                        @if(isset($items[$offer->offerId]) && $items[$offer->offerId] === $category->serviceCategoryRefId)
                        border border-blue-500
                        @else
                        border border-gray-200
                        @endif
                        rounded-xl p-4 cursor-pointer hover:shadow-sm flex flex-col justify-between min-h-[120px]">

                            <div class="flex items-start gap-3">
                                <input type="radio"
                                       class="h-4 w-4 mt-1 text-blue-600"
                                       wire:key="offer-{{ $offer->offerId }}-{{ $category->serviceCategoryRefId }}"
                                       name="items.{{ $offer->offerId }}"
                                       wire:model="items.{{ $offer->offerId }}"
                                       wire:click="changeBox('{{$offer->offerId}}', '{{$category->serviceCategoryRefId}}')"
                                       value="{{ $category->serviceCategoryRefId }}"
                                >
                                <span class="text-gray-900 font-medium">{{$category->name}}</span>
                            </div>

                            @if($category->serviceCategoryRefId !== $this->getNoSelectionValue())
                             <div class="flex flex-col items-end mt-4">
                                <span class="font-semibold text-gray-900">
                                    {{ Number::currency($category->salesPrice->amount, $category->salesPrice->currency) }}
                                </span>
                                <span class="text-xs text-gray-500">{{str($category->priceType)->headline()}}</span>
                            </div>
                            @endif
                        </label>
                    @endforeach

                </div>

                @error('items.' .$offer->offerId)
                <p class="text-red-600 text-sm">{{trans('checkout::input.validations.must_be_selected')}}</p>
                @enderror

            </div>

        @endforeach
    </form>


        <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>
        <div class="flex justify-between items-center">
            <div></div>
            <button type="button" wire:click="next"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                {{trans('checkout::page.trip_details.next')}}
            </button>
        </div>
    </div>
</x-checkout::editable-box>
