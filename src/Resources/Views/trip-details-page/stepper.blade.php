<div class="mb-8">
    <div class="relative w-full grid grid-cols-3 gap-0">
        <!-- Step 1 -->
        <div
            wire:click="navigate('trip.details')"
            class="flex flex-col items-center relative z-10 w-full cursor-pointer"
        >
            <div class="absolute top-3 left-0 w-full h-0.5 {{ $this->isActive('traveler-details') || $this->isCompleted(1) ? 'bg-blue-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            <div class="flex items-center justify-center w-6 h-6 rounded-full text-xs relative z-20 {{ $this->isActive('trip.details') ? 'bg-blue-500 text-white shadow-[0px_0px_0px_4px] shadow-blue-300' : ($this->isCompleted(1) ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300') }}">
                @if($this->isCompleted(1))
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <span class="text-[12px]">1</span>
                @endif
            </div>
            <div class="mt-2 text-[12px] {{ $this->isActive('trip.details') ? 'font-bold text-gray-800 dark:text-gray-200' : 'font-medium text-gray-600 dark:text-gray-400' }}">
                {{trans('checkout::page.trip_details.trip_details')}}
            </div>
        </div>

        <!-- Step 2 -->
        <div
            wire:click="navigate('payment')"
            class="flex flex-col items-center relative z-10 w-full cursor-pointer"
        >
            <div class="absolute top-3 left-0 w-full h-0.5 {{ $this->isActive('payment') || $this->isCompleted(2) ? 'bg-blue-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            <div class="flex items-center justify-center w-6 h-6 rounded-full text-xs relative z-20 {{ $this->isActive('payment') ? 'bg-blue-500 text-white shadow-[0px_0px_0px_4px] shadow-blue-300' : ($this->isCompleted(2) ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300') }}">
                @if($this->isCompleted(2))
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <span class="text-[12px]">2</span>
                @endif
            </div>
            <div class="mt-2 text-[12px] {{ $this->isActive('payment') ? 'font-bold text-gray-800 dark:text-gray-200' : 'font-medium text-gray-600 dark:text-gray-400' }}">
                {{trans('checkout::page.payment.payment')}}
            </div>
        </div>

        <!-- Step 3 -->
        <div
            wire:click="navigate('confirmation')"
            class="flex flex-col items-center relative z-10 w-full cursor-pointer"
        >
            <div class="absolute top-3 left-0 w-full h-0.5 {{ $this->isActive('confirmation') || $this->isCompleted(3) ? 'bg-blue-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            <div class="flex items-center justify-center w-6 h-6 rounded-full text-xs relative z-20 {{ $this->isActive('confirmation') ? 'bg-blue-500 text-white shadow-[0px_0px_0px_4px] shadow-blue-300' : ($this->isCompleted(3) ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300') }}">
                @if($this->isCompleted(3))
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <span class="text-[12px]">3</span>
                @endif
            </div>
            <div class="mt-2 text-[12px] {{ $this->isActive('confirmation') ? 'font-bold text-gray-800 dark:text-gray-200' : 'font-medium text-gray-600 dark:text-gray-400' }}">
                {{trans('checkout::page.booking_confirmation.booking_confirmation')}}
            </div>
        </div>
    </div>
</div>
