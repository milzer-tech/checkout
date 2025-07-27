@php
    $state = $isExpanded ? 'editing' : 'disabled';
@endphp

<x-checkout::editable-box
    title="Add Promo code"
    :state="$state"
    :showEdit="false"
    :showCheck="false"
>
    <div class="space-y-4">
        <form wire:submit="save" class="mb-6">
            <div class="flex gap-4 items-start">
                <div class="flex-1">
                    <input type="text" wire:model="promoCode" placeholder="Enter promo code"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           @if(!$isExpanded) disabled @endif>
                    @error('promoCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @session('failedPromoCode') <span class="text-red-500 text-sm">{{ $value  }}</span> @endsession

                </div>
                <button type="submit" class="bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 px-6 py-2 rounded-md"
                        @if(!$isExpanded) disabled @endif>
                    <span class="inline-flex items-center gap-2">
                        <span>Apply</span>

                        <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 text-blue-500"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                    </span>
                </button>
            </div>
        </form>

        @if($prices->promoCode)
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Promo code {{$promoCode}} applied! You saved {{\Illuminate\Support\Number::percentage($prices->decreasePercent())}} on your booking.</span>
                </div>
            </div>
        @endif


        <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

        <div class="flex justify-between items-center">
            {{--            <a href="#" wire:click.prevent="skipPromoCode" class="text-blue-600 hover:underline font-medium">--}}
            {{--                I don’t have a promo code--}}
            {{--            </a>--}}
            <div></div>

            <button type="button"
                    wire:click="proceedWithoutPromo"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                Next
            </button>
        </div>

    </div>
</x-checkout::editable-box>
