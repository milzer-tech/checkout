@use(Nezasa\Checkout\Enums\Section)
@php($state = $isExpanded ? 'editing' : 'valid')

@if($shouldRender)
    <x-checkout::editable-box
        title="{{trans('checkout::page.trip_details.activity_details')}}"
        :state="$state"
        :showEdit="true"
        :showCheck="$isCompleted"
        onEdit="expand('{{Section::Activity->value}}')"
    >
        <div class="space-y-4">


            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

            <div class="flex justify-between items-center">
                <button type="button"
                        wire:click="next"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    {{trans('checkout::page.trip_details.back')}}
                </button>

                <button type="button"
                        wire:click="next"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    {{trans('checkout::page.trip_details.next')}}
                </button>
            </div>

        </div>
    </x-checkout::editable-box>
@else

<div class="invisible"></div>
@endif
