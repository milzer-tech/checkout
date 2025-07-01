<div class="flex flex-col min-h-screen">
    <div class="flex-grow space-y-6">
        <!-- Page header - removed the image from here -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold flex items-center gap-2">
                Booking confirmation <span class="text-2xl">🎉</span>
            </h1>
            <div class="bg-green-100 text-green-700 px-3 py-1 rounded-full flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Confirmed
            </div>
        </div>

        <!-- Main image and trip summary - moved image here -->
        <div class="overflow-hidden mb-6">
            <div class="p-0">
                <!-- Image moved here with full width -->
                <div class="w-full h-[170px] relative overflow-hidden">
                    <img src="/images/42912e66-032b-40fd-ab59-fb16306d9ad5.png" alt="Palma de Mallorca" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/50 flex items-end">
                        <div class="p-6 text-white">
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <h2 class="text-2xl font-bold">{{ $tripDetails['title'] }}</h2>

                    <div class="flex flex-wrap gap-8">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.53689 6.00181L8.26578 5.87381L8.46667 5.65159C9.36089 4.65959 9.58044 3.28892 9.04 2.0747C8.49778 0.855146 7.33245 0.0969238 6 0.0969238C4.66667 0.0969238 3.50222 0.855146 2.96 2.0747C2.41956 3.28892 2.63911 4.65959 3.53333 5.65159L3.73422 5.87381L3.46311 6.00181C1.35911 6.98848 0 9.11915 0 11.4303C0 11.7974 0.298668 12.0969 0.666664 12.0969H7.77778C8.14578 12.0969 8.44445 11.7974 8.44445 11.4303C8.44445 11.0631 8.14578 10.7636 7.77778 10.7636H1.37245L1.42667 10.4969C1.86844 8.33426 3.79111 6.76359 6 6.76359C8.57333 6.76359 10.6667 8.85693 10.6667 11.4303C10.6667 11.7974 10.9653 12.0969 11.3333 12.0969C11.7013 12.0969 12 11.7974 12 11.4303C12 9.11915 10.6409 6.98848 8.53689 6.00181ZM6 5.43026C4.89689 5.43026 4 4.53248 4 3.43026C4 2.32803 4.89689 1.43026 6 1.43026C7.10311 1.43026 8 2.32803 8 3.43026C8 4.53248 7.10311 5.43026 6 5.43026Z" fill="currentColor" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200">2 Adults, 2 Children</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200">Tue, 1 Apr 2025 - Sat, 5 Apr 2025</span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="viewFullItinerary" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-md">
                            View full itinerary
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Booking details card -->
            <div class="p-6 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    Your trip has been booked <span>🎉</span>
                </h2>

                <div class="space-y-4 divide-y">
                    <div class="py-4 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M3 7L12 13L21 7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">Booking reference</span>
                        </div>
                        <span class="text-gray-700 dark:text-gray-200">{{ $bookingReference }}</span>
                    </div>

                    <div class="py-4 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M16 3V7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M8 3V7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M3 11H21" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">Order date</span>
                        </div>
                        <span class="text-gray-700 dark:text-gray-200">{{ $orderDate }}</span>
                    </div>

                    <div class="py-4 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">Booking status</span>
                        </div>
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirmed
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <button wire:click="printBookingConfirmation" class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-md border border-gray-300 dark:border-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print booking confirmation
                    </button>
                </div>
            </div>

            <!-- Travel information card -->
            <div class="p-6 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                <h2 class="text-xl font-bold mb-4">Travel information</h2>

                <div class="space-y-4 divide-y">
                    <div class="py-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">Travel date</span>
                        </div>
                        <div class="pl-7 space-y-1">
                            <p class="text-gray-700 dark:text-gray-200">Tue, 1 Apr 2025 - Sat, 5 Apr 2025</p>
                            <p class="text-gray-700 dark:text-gray-200">4 nights</p>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.53689 6.00181L8.26578 5.87381L8.46667 5.65159C9.36089 4.65959 9.58044 3.28892 9.04 2.0747C8.49778 0.855146 7.33245 0.0969238 6 0.0969238C4.66667 0.0969238 3.50222 0.855146 2.96 2.0747C2.41956 3.28892 2.63911 4.65959 3.53333 5.65159L3.73422 5.87381L3.46311 6.00181C1.35911 6.98848 0 9.11915 0 11.4303C0 11.7974 0.298668 12.0969 0.666664 12.0969H7.77778C8.14578 12.0969 8.44445 11.7974 8.44445 11.4303C8.44445 11.0631 8.14578 10.7636 7.77778 10.7636H1.37245L1.42667 10.4969C1.86844 8.33426 3.79111 6.76359 6 6.76359C8.57333 6.76359 10.6667 8.85693 10.6667 11.4303C10.6667 11.7974 10.9653 12.0969 11.3333 12.0969C11.7013 12.0969 12 11.7974 12 11.4303C12 9.11915 10.6409 6.98848 8.53689 6.00181ZM6 5.43026C4.89689 5.43026 4 4.53248 4 3.43026C4 2.32803 4.89689 1.43026 6 1.43026C7.10311 1.43026 8 2.32803 8 3.43026C8 4.53248 7.10311 5.43026 6 5.43026Z" fill="currentColor" />
                            </svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">Travellers</span>
                        </div>
                        <div class="pl-7">
                            @foreach($travelers as $traveler)
                                <p class="text-gray-700 dark:text-gray-200">{{ $traveler }}</p>
                            @endforeach
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-medium text-gray-700 dark:text-gray-200">Booking details</span>
                        </div>
                        <div class="pl-7 space-y-4">
                            <div>
                                <h3 class="font-medium mb-2">Stay</h3>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-200">Hotel Sol Palma (4 nights)</span>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-medium mb-2">Flights</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-200">Lisbon - Palma (Tue, 1 Apr)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-200">Palma - Lisbon (Sat, 5 Apr)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-lg">Total paid (EUR)</span>
                            <span class="font-bold text-lg">{{ $totalPrice }} €</span>
                        </div>

                        <button wire:click="viewCancellationPolicy" class="w-full flex items-center justify-center gap-2 text-blue-500 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Cancelation policy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Need help section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 my-8">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        Need help?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">Contact our support team</p>
                </div>
                <button wire:click="contactSupport" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-md">
                    Contact support
                </button>
            </div>
        </div>
    </div>
</div>
