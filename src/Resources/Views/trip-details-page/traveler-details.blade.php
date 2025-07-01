@php
    if ($travelerExpanded) {
        $state = 'editing';
    } else {
        $state = 'valid';
    }
@endphp

<x-checkout::editable-box
    title="Traveller details"
    subtitle="Information of all the travellers as appearing in their travel documents."
    :state="$state"
    :showEdit="$state === 'valid'"
    :showCheck="$state === 'valid'"
    onEdit="editTraveler"
>
    <form wire:submit="save">
        {{-- Traveller 1 section --}}
        <div>
            <h4 class="text-lg font-semibold mb-6">Traveller 1</h4>
            {{-- First row - Name fields --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 min-w-0">
                <div class="space-y-2 w-full min-w-0">
                    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">First name</label>
                    <input type="text" wire:model="travelers.0.firstName" class="form-input w-full" placeholder="e.g. Harry">
                    @error('travelers.0.firstName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2 w-full min-w-0">
                    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Second name</label>
                    <input type="text" wire:model="travelers.0.secondName" class="form-input w-full" placeholder="e.g. James">
                    @error('travelers.0.secondName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2 w-full min-w-0">
                    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Last name</label>
                    <input type="text" wire:model="travelers.0.lastName" class="form-input w-full" placeholder="e.g. Potter">
                    @error('travelers.0.lastName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-6 mt-4 min-w-0">
                <div class="flex flex-col md:flex-row gap-4 w-full">
                    <div class="space-y-2 w-full min-w-0">
                        <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Nationality</label>
                        <select wire:model="travelers.0.nationality" class="form-select pr-8 w-full">
                            <option value="" disabled>Select</option>
                            <option value="PT">Portuguese</option>
                            <option value="ES">Spanish</option>
                            <option value="US">American</option>
                            <option value="UK">British</option>
                        </select>
                        @error('travelers.0.nationality') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2 w-full min-w-0">
                        <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Gender</label>
                        <select wire:model="travelers.0.gender" class="form-select pr-8 w-full">
                            <option value="" disabled>Select</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>
                        @error('travelers.0.gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="space-y-2 w-full min-w-0">
                    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Date of birth</label>
                    @include('checkout::components.date-field', [
                        'day' => 'travelers.0.dateOfBirthDay',
                        'month' => 'travelers.0.dateOfBirthMonth',
                        'year' => 'travelers.0.dateOfBirthYear'
                    ])
                    @error('travelers.0.dateOfBirthDay') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @error('travelers.0.dateOfBirthMonth') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @error('travelers.0.dateOfBirthYear') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-6 mt-4 min-w-0">
                <div class="flex flex-col md:flex-row gap-4 w-full">
                    <div class="space-y-2 w-full min-w-0">
                        <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Passport number</label>
                        <input type="text" wire:model="travelers.0.passportNumber" class="form-input w-full" placeholder="e.g. 1234">
                        @error('travelers.0.passportNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2 w-full min-w-0">
                        <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Passport issuing country</label>
                        <select wire:model="travelers.0.passportIssuingCountry" class="form-select pr-8 w-full">
                            <option value="" disabled>Select</option>
                            <option value="PT">Portugal</option>
                            <option value="ES">Spain</option>
                            <option value="US">United States</option>
                            <option value="UK">United Kingdom</option>
                        </select>
                        @error('travelers.0.passportIssuingCountry') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="space-y-2 w-full min-w-0">
                    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Passport expiration date</label>
                    @include('checkout::components.date-field', [
                        'day' => 'travelers.0.passportExpiryDay',
                        'month' => 'travelers.0.passportExpiryMonth',
                        'year' => 'travelers.0.passportExpiryYear'
                    ])
                    @error('travelers.0.passportExpiryDay') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @error('travelers.0.passportExpiryMonth') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @error('travelers.0.passportExpiryYear') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Next traveller button --}}
            <div class="flex justify-end mt-4">
                <button type="button" wire:click="$toggle('showSecondTraveler')" class="text-blue-600 hover:underline flex items-center gap-1 font-medium">
                    Next traveller
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Traveller 2 section --}}
        @if($showSecondTraveler)
            <div class="mt-10">
                <h4 class="text-lg font-semibold mb-6">Traveller 2</h4>
                <div class="text-gray-400 italic">
                    [Traveller 2 form fields placeholder]
                </div>
            </div>
        @endif

        {{-- Next button --}}
        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                Next
            </button>
        </div>
    </form>
</x-checkout::editable-box>
