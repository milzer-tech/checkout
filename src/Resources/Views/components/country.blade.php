@php
    use Nezasa\Checkout\Supporters\AutoCompleteSupporter;
    use Nezasa\Checkout\Supporters\CountryOptionsSupporter;

    $countryOptions = CountryOptionsSupporter::orderedForSelect($countriesResponse->countries, $name)
        ->map(fn ($country): array => [
            'value' => "{$country->iso_code}-{$country->name}",
            'label' => $country->name,
            'isoCode' => $country->iso_code,
        ])
        ->values();

    $selectedValue = data_get($this, $wireModel, '');
@endphp
<div class="space-y-2 w-full min-w-0">
    <label
        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
        {{trans("checkout::input.attributes.$name")}}@if($isRequired)*@endif
    </label>
    <div
        class="relative"
        x-data="{
            open: false,
            search: '',
            selectedValue: @entangle($wireModel).live,
            options: @js($countryOptions->all()),
            placeholder: @js(trans('checkout::input.placeholders.select')),
            get selectedOption() {
                return this.options.find((option) => option.value === this.selectedValue) ?? null;
            },
            get filteredOptions() {
                const term = this.search.trim().toLocaleLowerCase();

                if (term === '') {
                    return this.options;
                }

                return this.options.filter((option) => {
                    return `${option.label} ${option.isoCode}`.toLocaleLowerCase().includes(term);
                });
            },
            openDropdown() {
                this.open = true;
                this.$nextTick(() => this.$refs.search?.focus());
            },
            selectOption(option) {
                this.selectedValue = option.value;
                this.search = '';
                this.open = false;
                this.$refs.select.value = option.value;
            },
            init() {
                this.$nextTick(() => {
                    if (this.$refs.select.value) {
                        this.selectedValue = this.$refs.select.value;
                    }
                });
                this.$watch('selectedValue', (value) => {
                    this.$refs.select.value = value ?? '';
                });
            }
        }"
        @keydown.escape.window="open = false"
        @click.outside="open = false"
    >
        <select
            x-ref="select"
            name="{{$wireModel}}"
            wire:model.change="{{$wireModel}}"
            {{AutoCompleteSupporter::get($name)}}
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        >
            <option value="" @selected(blank($selectedValue))>{{ trans('checkout::input.placeholders.select') }}</option>
            @foreach($countryOptions as $country)
                <option value="{{$country['value']}}" @selected($selectedValue === $country['value']) wire:ignore>
                    {{$country['label']}}
                </option>
            @endforeach
        </select>

        <button
            type="button"
            class="form-input flex w-full items-center justify-between gap-3 text-left"
            :class="selectedOption ? 'form-input-filled' : 'form-input-empty'"
            @click="openDropdown()"
            :aria-expanded="open"
        >
            <span class="min-w-0 flex-1 truncate" x-text="selectedOption ? selectedOption.label : placeholder"></span>
            <svg class="h-4 w-4 flex-none text-gray-400 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            x-show="open"
            x-transition
            class="absolute z-30 mt-1 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
            style="display: none;"
        >
            <div class="border-b border-gray-100 p-2 dark:border-gray-600">
                <input
                    x-ref="search"
                    type="search"
                    x-model="search"
                    class="form-input w-full px-3 py-2"
                    placeholder="{{ trans("checkout::input.attributes.$name") }}"
                    @keydown.enter.prevent="if (filteredOptions.length > 0) selectOption(filteredOptions[0])"
                >
            </div>

            <ul class="max-h-60 overflow-auto py-1" role="listbox">
                <template x-for="option in filteredOptions" :key="option.value">
                    <li>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-4 py-2 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white"
                            :class="{ 'bg-blue-50 text-blue-700 dark:bg-gray-600 dark:text-white': selectedValue === option.value }"
                            @click="selectOption(option)"
                            role="option"
                            :aria-selected="selectedValue === option.value"
                        >
                            <span class="min-w-0 truncate" x-text="option.label"></span>
                            <span class="flex-none text-xs text-gray-400" x-text="option.isoCode"></span>
                        </button>
                    </li>
                </template>

                <li x-show="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">
                    {{ trans('checkout::input.placeholders.select') }}
                </li>
            </ul>
        </div>
    </div>
    @error($wireModel)<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
</div>
