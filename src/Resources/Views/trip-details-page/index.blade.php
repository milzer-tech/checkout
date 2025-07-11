<div class="flex flex-col min-h-screen">
    <h1 class="text-2xl font-bold mb-6">Trip details</h1>
    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr] gap-8">
        <div class="space-y-6">
            <section class="space-y-6">
                <livewire:banner />
                <livewire:contact-details :$contactRequirements/>
                <livewire:traveler-details />
                <livewire:promo-code-section />
                <livewire:travel-insurance-section />
                <livewire:additional-services-section />
                <livewire:payment-options-section />
                <div class="mt-6"></div>
            </section>
        </div>
        <div class="overflow-auto min-w-[300px]">
            <livewire:trip-summary :$itinerary/>
        </div>
    </div>
    <!-- Footer with navigation buttons - takes 2 columns out of 3 on larger screens -->
    <div class="mt-10 mb-6 flex justify-between max-w-full md:max-w-[66.66%]">
        <button wire:click="goBack" class="flex items-center gap-2 px-6 py-3 rounded-md border border-gray-300 dark:border-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </button>
        <button wire:click="goToPayment" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-md">
            Pay 1 € (EUR)
        </button>
    </div>
    <div class="text-center mb-10 text-gray-500 dark:text-gray-400 max-w-full md:max-w-[66.66%]">
        Copyright 2025 Squad Ruby Tours. All rights reserved
    </div>
</div>
