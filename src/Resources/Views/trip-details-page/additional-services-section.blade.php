@use(Illuminate\Support\Number)
@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="Additional services"
    state="editing"
    :showEdit="true"
    :showCheck="true"
    class="{{$upsellItemsResponse->offers->isEmpty() ? 'hidden' : ''}}"
    onEdit="expand('{{Section::AdditionalService->value}}')"
>

    <div class="space-y-8">

        @foreach($upsellItemsResponse->offers as $offer)
            <!-- Welcome Drink Section -->
            <div class="space-y-4">
                <h2 class="text-base font-semibold text-gray-900">{{ucfirst($offer->name)}}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($offer->serviceCategories as $service)
                        <label
                            class="border border-gray-200 rounded-xl p-4 flex flex-col justify-between cursor-pointer hover:shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="radio" name="{{$offer->offerId}}"
                                           @if($items[$offer->offerId][$service->serviceCategoryRefId] > 0)
                                               checked
                                           @elseif($service->serviceCategoryRefId === $offer->description)
                                                checked
                                           @endif
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <span class="text-gray-800">{{ucfirst($service->name)}}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-semibold">{{Number::currency($service->salesPrice->amount, $service->salesPrice->currency)}}</span>
                                <div class="flex items-center gap-2 text-gray-500">
                                    <button wire:click="removeItem(false, '{{$offer->offerId}}', '{{$service->serviceCategoryRefId}}')" class="text-xl leading-none opacity-50">−</button>
                                    <span class="text-black cursor-default">{{$items[$offer->offerId][$service->serviceCategoryRefId]}}</span>
                                    <button wire:click="addItem(false, '{{$offer->offerId}}', '{{$service->serviceCategoryRefId}}')" class="text-blue-600 text-xl leading-none">+</button>
                                </div>
                            </div>
                        </label>
                    @endforeach

                    @if($offer->optOutPossible)
                            <label
                                class="border border-blue-700 rounded-xl px-4 py-2 flex items-center gap-2 cursor-pointer hover:shadow-sm w-full h-[48px]">
                                <input type="radio"
                                       name="{{$offer->offerId}}"
                                       wire:click="noNeed(false, '{{$offer->offerId}}', '{{$service->serviceCategoryRefId}}')"
                                       @checked(collect($items[$offer->offerId])->filter()->isEmpty())
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-gray-900 font-medium text-sm">No need</span>
                            </label>
                    @endif
                </div>
            </div>
        @endforeach

    </div>


    {{--    <div class="space-y-4">--}}
    {{--        <p class="text-sm text-gray-600">--}}
    {{--            Here are some products you might find useful during your trip.--}}
    {{--        </p>--}}

    {{--        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">--}}
    {{--            <label class="border border-gray-200 rounded-2xl p-4 w-full cursor-pointer">--}}
    {{--                <!-- Top row: radio + title -->--}}
    {{--                <div class="flex items-start space-x-2">--}}
    {{--                    <input type="radio" name="insurance" class="mt-1 h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500" checked>--}}
    {{--                    <span class="text-gray-900 font-semibold text-base">Travel Basic</span>--}}
    {{--                </div>--}}

    {{--                <!-- Price -->--}}
    {{--                <div class="text-emerald-500 font-semibold text-sm mt-1 mb-2 pl-7">--}}
    {{--                    + 2.31 € per day--}}
    {{--                </div>--}}

    {{--                <!-- Text/content (aligned with radio) -->--}}
    {{--                <div class="text-sm text-gray-800 leading-relaxed pl-7">--}}
    {{--                    <p>--}}
    {{--                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.--}}
    {{--                    </p>--}}
    {{--                </div>--}}
    {{--            </label>--}}
    {{--            <label class="border border-gray-200 rounded-2xl p-4 w-full cursor-pointer">--}}
    {{--                <!-- Top row: radio + title -->--}}
    {{--                <div class="flex items-start space-x-2">--}}
    {{--                    <input type="radio" name="insurance" class="mt-1 h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500" checked>--}}
    {{--                    <span class="text-gray-900 font-semibold text-base">Travel Basic</span>--}}
    {{--                </div>--}}

    {{--                <!-- Price -->--}}
    {{--                <div class="text-emerald-500 font-semibold text-sm mt-1 mb-2 pl-7">--}}
    {{--                    + 2.31 € per day--}}
    {{--                </div>--}}

    {{--                <!-- Text/content (aligned with radio) -->--}}
    {{--                <div class="text-sm text-gray-800 leading-relaxed pl-7">--}}
    {{--                    <p>--}}
    {{--                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.--}}
    {{--                    </p>--}}
    {{--                </div>--}}
    {{--            </label>--}}
    {{--            <label class="border border-gray-200 rounded-2xl p-4 w-full cursor-pointer">--}}
    {{--                <!-- Top row: radio + title -->--}}
    {{--                <div class="flex items-start space-x-2">--}}
    {{--                    <input type="radio" name="insurance" class="mt-1 h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500" checked>--}}
    {{--                    <span class="text-gray-900 font-semibold text-base">Travel Basic</span>--}}
    {{--                </div>--}}

    {{--                <!-- Price -->--}}
    {{--                <div class="text-emerald-500 font-semibold text-sm mt-1 mb-2 pl-7">--}}
    {{--                    + 2.31 € per day--}}
    {{--                </div>--}}

    {{--                <!-- Text/content (aligned with radio) -->--}}
    {{--                <div class="text-sm text-gray-800 leading-relaxed pl-7">--}}
    {{--                    <p>--}}
    {{--                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.--}}
    {{--                    </p>--}}
    {{--                </div>--}}
    {{--            </label>--}}
    {{--            <label class="border border-gray-200 rounded-2xl p-4 w-full cursor-pointer">--}}
    {{--                <!-- Top row: radio + title -->--}}
    {{--                <div class="flex items-start space-x-2">--}}
    {{--                    <input type="radio" name="insurance" class="mt-1 h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500" checked>--}}
    {{--                    <span class="text-gray-900 font-semibold text-base">Travel Basic</span>--}}
    {{--                </div>--}}

    {{--                <!-- Price -->--}}
    {{--                <div class="text-emerald-500 font-semibold text-sm mt-1 mb-2 pl-7">--}}
    {{--                    + 2.31 € per day--}}
    {{--                </div>--}}

    {{--                <!-- Text/content (aligned with radio) -->--}}
    {{--                <div class="text-sm text-gray-800 leading-relaxed pl-7">--}}
    {{--                    <p>--}}
    {{--                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.--}}
    {{--                    </p>--}}
    {{--                </div>--}}
    {{--            </label>--}}

    {{--        </div>--}}

    {{--        <div class="flex justify-end pt-4">--}}
    {{--            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition">--}}
    {{--                Next--}}
    {{--            </button>--}}
    {{--        </div>--}}
    {{--    </div>--}}

</x-checkout::editable-box>
