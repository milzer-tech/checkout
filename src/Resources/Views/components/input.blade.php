@php use Nezasa\Checkout\Supporters\AutoCompleteSupporter; @endphp
<div class="space-y-2 w-full min-w-0">
    <label class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name")}}@if($isRequired)*@endif
    </label>
     <input
     name="{{$wireModel}}"
     type=@switch($label)
     @case('email')
      "email"
      @break
      @default
      "text"
      @endswitch
       wire:model.blur="{{$wireModel}}"
       {{AutocompleteSupporter::get($name)}}
        class="form-input w-full" placeholder="{{trans("checkout::input.placeholders.$name")}}"/>
      @error($wireModel)<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>
