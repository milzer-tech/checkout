
@if($availability?->isOpen() || $availability?->isBooked())
<span class="inline-flex items-center px-3 py-1 bg-[#F2FCE2] dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm rounded-full">
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
         xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    @if($this->getName() === 'payment-result-page' && $availability?->isBooked())
        {{trans('checkout::page.trip_details.booked')}}
    @else
        {{trans('checkout::page.trip_details.available')}}
    @endif
</span>
@endif

@if($availability?->isOnRequest())
<span class="inline-flex items-center px-3 py-1 bg-[#DBEAFE] dark:bg-blue-900/30 text-black text-sm rounded-full">
    <svg class="w-4 h-4 mr-2 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 4v8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        <circle cx="12" cy="17" r="2" fill="currentColor"/>
    </svg>
    {{trans('checkout::page.trip_details.on_request')}}
</span>
@endif


@if($availability?->isNone())
<span class="inline-flex items-center px-3 py-1 bg-[#FEE2E2] dark:bg-red-900/30 text-black text-sm rounded-full">
    <svg class="w-4 h-4 mr-2 text-red-500 dark:text-red-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" stroke="currentColor"/>
    </svg>
    {{trans('checkout::page.trip_details.unavailable')}}
</span>
@endif



{{--<span class="inline-flex items-center px-3 py-1 bg-[#FEF3C7] dark:bg-yellow-900/30 text-black text-sm rounded-full">--}}
{{--    <svg class="w-4 h-4 mr-2 text-yellow-500 dark:text-yellow-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">--}}
{{--        <path d="M12 4v8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>--}}
{{--        <circle cx="12" cy="17" r="2" fill="currentColor"/>--}}
{{--    </svg>--}}
{{--    Update--}}
{{--</span>--}}

