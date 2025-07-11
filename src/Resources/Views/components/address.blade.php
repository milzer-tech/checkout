<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.street1")}}
    </label>
     <input type="text" wire:model="{{$wireModel}}.street1" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.street1")}}" />
      @error($wireModel.'street1')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>

<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.street2")}}
    </label>
    <input type="text" wire:model="{{$wireModel}}.street2" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.street2")}}" />
    @error($wireModel.'street2')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>

<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.postalCode")}}
    </label>
    <input type="text" wire:model="{{$wireModel}}.postalCode" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.postalCode")}}" />
    @error($wireModel.'postalCode')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>

<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.city")}}
    </label>
    <input type="text" wire:model="{{$wireModel}}.city" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.city")}}" />
    @error($wireModel.'city')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>

<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.country")}}
    </label>
    <input type="text" wire:model="{{$wireModel}}.country" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.country")}}" />
    @error($wireModel.'country')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>

<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.countryCode")}}
    </label>
    <input type="text" wire:model="{{$wireModel}}.countryCode" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.countryCode")}}" />
    @error($wireModel.'countryCode')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>

<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name.region")}}
    </label>
    <input type="text" wire:model="{{$wireModel}}.region" class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name.region")}}" />
    @error($wireModel.'region')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>
