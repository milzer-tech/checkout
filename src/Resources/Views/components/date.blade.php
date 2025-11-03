<div class="space-y-2 w-full min-w-0">
    <label
        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden"> {{trans("checkout::input.attributes.$name")}}@if($isRequired)*@endif</label>
    <div class="date-field form-input w-full flex-1 p-0">
        <div class="p-0 flex justify-evenly overflow-visible py-0">
            <input type="text" wire:model.blur="{{$wireModel}}.day" maxlength="2" placeholder="DD"
                   class="form-input w-[20%] px-4  min-w-[55px]">
            <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
            <div class="relative w-[40%]">
                <select wire:model.change="{{$wireModel}}.month"
                        class="form-input custom-select w-full appearance-none px-2 pr-8">
                    <option value="" disabled="">Month</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                <svg class="h-4 w-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path
                        d="M6 9l6 6 6-6"></path></svg>
            </span>
            </div>
            <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
            <input type="text" wire:model.blur="{{$wireModel}}.year" maxlength="4" placeholder="YYYY"
                   class="form-input w-[80px] px-4 ">
        </div>
    </div>


    @error($wireModel . '.day')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @else
        @error($wireModel . '.month')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @else
            @error($wireModel . '.year')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
        @enderror

    @enderror


</div>
