<div class="flex flex-col min-h-screen">
    <div class="flex-grow grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section>
                <h1 class="text-2xl font-bold mb-6">Payment</h1>

                <div class="p-8 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                    <div class="flex items-center justify-center h-48">
                        <p class="text-lg text-gray-700 dark:text-gray-300">
                            External payment method will open
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <div class="lg:col-span-1">
            <livewire:trip-summary />
        </div>
    </div>

    <!-- Footer with navigation and copyright -->
    <div class="mt-10 mb-6">
        <div class="flex justify-between items-center">
            <button wire:click="goBack" class="flex items-center gap-2 px-6 py-3 rounded-md border border-gray-300 dark:border-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </button>

            <button wire:click="goToPayment" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-md">
                Pay {{ $totalPrice }} € (EUR)
            </button>
        </div>

        <div class="text-center mt-10 text-gray-500 dark:text-gray-400">
            Copyright 2025 Squad Ruby Tours. All rights reserved
        </div>
    </div>
</div>
