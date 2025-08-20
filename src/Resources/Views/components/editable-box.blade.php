@props([
    'title',
    'subtitle' => null,
    'state' => 'editing', // valid, editing, collapsed, disabled
    'onEdit' => null,
    'showEdit' => false,
    'showCheck' => false,
    'class' => '',
])

@php
    $boxClasses = [
        'border', 'rounded-lg', 'transition-all', 'duration-300',
        'overflow-hidden', 'mb-6',
        'bg-white', 'dark:bg-gray-800',
        'border-gray-200', 'dark:border-gray-600',
        'shadow-sm',
    ];
    if ($state === 'disabled' || $state === 'collapsed') {
        $boxClasses = [
            'border', 'rounded-lg', 'transition-all', 'duration-300',
            'overflow-hidden', 'mb-6',
            'bg-transparent',
            'border-[#E0E2E8]',
            'shadow-sm',
        ];
    } elseif ($state === 'editing') {
        $boxClasses = [
            'rounded-lg', 'transition-all', 'duration-300',
            'overflow-hidden', 'mb-6',
            'bg-white', 'dark:bg-gray-800',
        ];
    } elseif ($state === 'collapsed') {
        $boxClasses[] = 'cursor-pointer';
    }
@endphp

<div class="{{ implode(' ', $boxClasses) }} {{ $class }} @if($state === 'editing') shadow-[0px_0px_20px_0px_#7D82934D] dark:shadow-none @endif">
    <div class="flex justify-between items-center px-8 py-6 select-none rounded-[12px]">
        <div class="flex items-center gap-4">
            @if($showCheck)
                <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.21027 13.6667C6.88052 13.6667 6.56396 13.5443 6.32968 13.3261L0.370311 7.77546C-0.12036 7.31849 -0.12387 6.57419 0.362479 6.11315C0.848776 5.65218 1.64081 5.64885 2.13148 6.1058L7.20071 10.8272L17.859 0.682873C18.3444 0.221015 19.1363 0.21626 19.6279 0.672243C20.1196 1.12833 20.1246 1.87258 19.6393 2.33454L8.10041 13.3171C7.86553 13.5407 7.5426 13.6655 7.21027 13.6667Z" fill="#19A974"/>
                </svg>
            @endif
            <div>
                <h3 class="dark:text-white" style="font-family: Mulish; font-weight: 600; font-size: 18px; line-height: 24px; vertical-align: middle;">{{ $title }}</h3>
                @if($subtitle)
                    <div style="font-family: Mulish; font-weight: 400; font-size: 16px; line-height: 24px; letter-spacing: 0px; vertical-align: middle;" class="text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</div>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-4">
            @if($showEdit && $state === 'valid')
                <button @if($onEdit) wire:click.stop="{{ $onEdit }}" @endif class="text-blue-600 font-medium hover:underline focus:outline-none bg-transparent px-2 py-1 rounded transition">
                    {{trans('checkout::page.trip_details.edit')}}
                </button>
            @endif
        </div>
    </div>
    @if($state === 'editing')
    <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>
        <div class="px-8 py-6 bg-white dark:bg-gray-800">
            {{ $slot }}
        </div>
    @endif
</div>
