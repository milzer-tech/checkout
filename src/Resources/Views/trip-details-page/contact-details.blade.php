@php
    if ($contactExpanded) {
        $state = 'editing';
    } else {
        $state = 'valid';
    }
@endphp

<x-checkout::editable-box
    title="Contact details"
    :state="$state"
    :showEdit="$state === 'valid'"
    :showCheck="$state === 'valid'"
    onEdit="editContact"
>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-8">
            <div class="col-span-1 md:col-span-6 space-y-2 w-full">
                <label class="block text-gray-700 dark:text-gray-200 font-medium">
                    Email
                </label>
                <input
                    type="email"
                    wire:model="email"
                    placeholder="example@mail.com"
                    class="form-input"
                    required
                />
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="col-span-1 md:col-span-6 space-y-2 w-full">
                <label class="block text-gray-700 dark:text-gray-200 font-medium">
                    Phone number
                </label>
                <input
                    type="tel"
                    wire:model="phone"
                    placeholder="+1 (234) 567-8901"
                    class="form-input"
                    required
                />
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
            >
                Save Contact Details
            </button>
        </div>
    </form>
</x-checkout::editable-box>
