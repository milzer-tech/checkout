@use(Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum)
@use(Nezasa\Checkout\Supporters\AutocompleteSupporter)
<div class="space-y-2 w-full min-w-0">
    <label
        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans('checkout::input.attributes.gender')}}@if($isRequired)*@endif
    </label>
    <select wire:model.change="{{$wireModel}}" {{AutocompleteSupporter::get('')}} class="form-select pr-8 w-full">
        <option>Select</option>
        @foreach(GenderEnum::getLabels() as $value => $lable)
            <option value="{{$value}}">{{$lable}}</option>
        @endforeach
    </select>
    @error($wireModel)<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>
