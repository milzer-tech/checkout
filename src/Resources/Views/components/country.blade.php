<div class="space-y-2 w-full min-w-0">
    <label
        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name")}}
    </label>
    <select wire:model.change="{{$wireModel}}" class="form-select pr-8 w-full">
        @foreach($countriesResponse->countries as $country)
            <option value="{{$country->iso_code}}-{{$country->name}}" @selected($country->name === 'Albania') wire:ignore>{{$country->name}}</option>
        @endforeach
    </select>
    @error($wireModel)<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>
