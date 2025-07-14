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
        @php($inputs = 0)
        @php($openTag = false)

        @foreach($contactRequirements as $name => $value)
            @if($value->isHidden())
                @continue
            @endif

            @if($inputs === 0 && !$openTag)
                @php($openTag = true)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 min-w-0">
                    @endif

                    @if(! in_array($name, ['address1', 'address2', 'gender', 'mobilePhone']))
                        @include('checkout::components.input', [
                            'label' => $name,
                            'wireModel' => "contact.$name",
                            'placeholder' => $name,
                        ])
                        @php($inputs++)
                    @endif

                    @if($name === 'gender')
                        @include('checkout::components.gender', ['wireModel' => "contact.$name"])
                        @php($inputs++)
                    @endif

                    @if($name === 'mobilePhone')
                        @include('checkout::components.phone', [
                            'wireModel' => "contact.$name",
                            'codes' => $countryCodes
                           ])
                        @php($inputs++)
                    @endif

                @if($inputs === 3 && $openTag)
                    </div>
                    @php($inputs = 0)
                    @php($openTag = false)
                @endif

                @endforeach

                @if($openTag)
                        </div>
                @endif


                @unless($contactRequirements->address1->isHidden())
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 min-w-0">
                        @include('checkout::components.address', ['wireModel' => "contact.address1", 'name' => 'address1'])
                    </div>
                        @php($inputs++)
                @endunless

                @unless($contactRequirements->address2->isHidden())
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 min-w-0">
                        @include('checkout::components.address', ['wireModel' => "contact.address2", 'name' => 'address2'])
                    </div>
                    @php($inputs++)
                @endunless


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
