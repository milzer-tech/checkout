@use(Nezasa\Checkout\Enums\Section)
@php
    $state = $isExpanded ? 'editing' : 'valid';
@endphp

<x-checkout::editable-box
        title="{{trans('checkout::page.trip_details.contact_details')}}"
        :state="$state"
        :showEdit="$state === 'valid'"
        :showCheck="$isCompleted"
        onEdit="expand('{{Section::Contact->value}}')"
>
    <form wire:submit="save" class="space-y-6">

        @include('checkout::blades.inputs',[
            'requirements' => $contactRequirements,
            'countryCodes' => $countryCodes,
            'saveTo' => 'contact'
])

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
                >
                   {{trans('checkout::page.trip_details.save_contact')}}
                </button>
            </div>
    </form>
</x-checkout::editable-box>
