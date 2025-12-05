<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 min-w-0 mb-4">
    <div class="space-y-2 w-full min-w-0 lg:col-span-3">
        <label
            class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
            {{trans('checkout::page.trip_details.travel_reason')}}*
        </label>
        <select name="test" wire:model.change="paxInfo.{{$roomNumber}}.{{$i}}.travel_reason"
                class="form-select pr-8 w-full">
            <option value="">Select</option>
            @foreach(config()->array('checkout::cuba-travel.reasons') as $index => $option)
                <option value="{{$option}}">{{$option}}</option>
            @endforeach
        </select>
        @error("paxInfo.$roomNumber.$i.travel_reason")<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
    </div>

</div>
