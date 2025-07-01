@php
    $state = 'disabled';
@endphp

<x-checkout::editable-box
    title="Additional services"
    :state="$state"
    :showEdit="false"
    :showCheck="false"
>
    <div class="space-y-4">
        @foreach($services as $service)
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 {{ in_array($service['id'], $selectedServices) ? 'border-blue-500 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20' : '' }} opacity-50 pointer-events-none">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <input type="checkbox"
                               id="service_{{ $service['id'] }}"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" disabled>
                    </div>
                    <div class="flex-1">
                        <label for="service_{{ $service['id'] }}" class="block">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-semibold">{{ $service['name'] }}</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $service['description'] }}</p>
                                </div>
                                <span class="font-semibold">${{ $service['price'] }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-checkout::editable-box>
