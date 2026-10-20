<div {{ $attributes->merge([
    'role' => 'status',
    'class' => 'flex items-center justify-center gap-3 px-8 py-6 mb-6 rounded-lg bg-white dark:bg-gray-800 shadow-[0px_0px_20px_0px_#7D82934D] dark:shadow-none text-gray-700 dark:text-gray-200',
]) }}>
    <svg class="h-5 w-5 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
         viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
    </svg>
    <span class="text-sm">{{ trans('checkout::page.trip_details.loading') }}</span>
</div>
