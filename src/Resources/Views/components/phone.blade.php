<div class="space-y-2 w-full min-w-0">
    <label
        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans('checkout::input.attributes.mobilePhone')}}
    </label>
    <div class="date-field form-input w-full flex-1 p-0">
        <div class="p-0 flex justify-evenly overflow-visible py-0">

            <div class="relative flex-[1]">
                <select class="form-input custom-select w-full appearance-none px-2 pr-8"
                        wire:model="travelers.0.dateOfBirthMonth">
                   @foreach($codes->callingCodes->sortBy('callingCode')->unique('callingCode') as $code)
                        <option value="{{$code->callingCode}}">+{{$code->callingCode}}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                <svg class="h-4 w-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path
                        d="M6 9l6 6 6-6"></path></svg>
            </span>
            </div>
            <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
            <input type="text" wire:model="{{$wireModel}}" class="form-input w-[80px] px-4 ">
            @error($wireModel)<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
