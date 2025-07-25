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
        <form wire:submit="save">
        <div class="flex gap-4">
            <div class="flex-1">
                <input type="text" wire:model="promoCode" placeholder="Enter promo code"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                       @if(!$isExpanded) disabled @endif>
                @error('promoCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            </div>
            <button type="submit"  class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
                    @if(!$isExpanded) disabled @endif>
                Apply
            </button>
        </div>
        </form>

{{--        @if($isValid)--}}
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Promo code applied! You get 1111% off</span>
                </div>
            </div>
{{--        @endif--}}
    </div>
</x-checkout::editable-box>
