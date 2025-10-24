<div class="space-y-2 w-full min-w-0">
    <label
        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{ trans('checkout::input.attributes.mobilePhone') }}
    </label>

    <div class="date-field form-input w-full flex-1 p-0">
        <div class="p-0 flex justify-evenly overflow-visible py-0">

            <!-- Country calling code select -->
            <div class="relative w-[90px] flex-shrink-0">
                <select class="form-input custom-select w-full appearance-none px-2 pr-8"
                       wire:model.change="{{$wireModel.'.countryCode'}}">
                    @foreach($codes->callingCodes->sortBy('callingCode')->unique('callingCode') as $code)
                        <option value="{{ $code->callingCode }}">+{{ $code->callingCode }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6"></path>
                    </svg>
                </span>
            </div>

            <!-- Divider -->
            <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>

            <!-- Phone number input -->
            <div class="flex-1">

                <input type="text" wire:model.blur="{{ $wireModel.'.phoneNumber' }}" class="form-input w-full px-4">

            </div>

        </div>

    </div>
    @error($wireModel.'.phoneNumber')
    <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
