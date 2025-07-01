<div class="date-field form-input w-full flex-1 p-0">
    @if (!empty($title))
        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">{{ $title }}</label>
    @endif
    <div class="p-0 flex justify-evenly overflow-visible py-0">
        <input type="text" maxlength="2" placeholder="DD" class="form-input w-[20%] px-4  min-w-[55px]" wire:model="{{ $day }}">
        <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
        <div class="relative w-[40%]">
            <select class="form-input custom-select w-full appearance-none px-2 pr-8" wire:model="{{ $month }}">
                <option value="" disabled>Month</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                <svg class="h-4 w-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </span>
        </div>
        <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
        <input type="text" maxlength="4" placeholder="YYYY" class="form-input w-[80px] px-4 " wire:model="{{ $year }}">
    </div>
</div>
