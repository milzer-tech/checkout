<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans('checkout::input.attributes.gender')}}
    </label>
    <select wire:model="{{$wireModel}}" class="form-select pr-8 w-full">
        <option>Select</option>
        <option value="M">Male</option>
        <option value="F">Female</option>
    </select>
      @error($wireModel)<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>
