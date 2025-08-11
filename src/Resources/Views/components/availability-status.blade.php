@if($availability?->isOpen())
    <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
@endif

@if($availability?->isNone())
<svg class="w-4 h-4 mr-2 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/>
</svg>

@endif

@if($availability?->isBooked())
    <svg class="w-4 h-4 mr-2 text-yellow-500 dark:text-yellow-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 4v8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        <circle cx="12" cy="17" r="2" fill="currentColor"/>
    </svg>
@endif

@if($availability?->isOnRequest())
    <svg class="w-4 h-4 mr-2 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 4v8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        <circle cx="12" cy="17" r="2" fill="currentColor"/>
    </svg>
@endif


@if(is_null($availability))
    <svg class="w-4 h-4 mr-2 text-blue-500 dark:text-blue-400 invisible" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    </svg>
@endif
