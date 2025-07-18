@php
    use Nezasa\Checkout\Integrations\Nezasa\Enums\TravelerRequirementFieldEnum;

    $state = $contactExpanded ? 'editing' : 'valid';
@endphp

<x-checkout::editable-box
        title="Contact details"
        :state="$state"
        :showEdit="$state === 'valid'"
        :showCheck="$state === 'valid'"
        onEdit="editContact"
>
    <form wire:submit="save" class="space-y-6">

        @include('checkout::trip-details-page.inputs',[
            'requirements' => $contactRequirements,
            'countryCodes' => $countryCodes,
            'saveTo' => 'contact'
])

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
                >
                    Save Contact
                </button>
            </div>
    </form>
</x-checkout::editable-box>
