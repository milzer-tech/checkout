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


        <!-- ONE card for image + content (matches Figma) -->
        <div class="rounded-2xl overflow-hidden border border-gray-200 bg-white dark:bg-gray-800 shadow-sm">
            <!-- Image with clipped corners -->
            <div class="relative w-full h-[220px]">
                <img
                    src="https://thumbs.dreamstime.com/b/luxury-hotel-bellagio-las-vegas-nv-june-june-usa-casino-located-36820850.jpg"
                    alt="Palma de Mallorca"
                    class="w-full h-full object-cover"
                />
            </div>

            <!-- Content -->
            <div class="p-6 border-t border-gray-100 flex justify-between items-center">
                <!-- Left: title + details -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">{{ $tripDetails['title'] }}</h2>

                    <div class="flex flex-wrap items-center gap-6 text-gray-700 dark:text-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.53689 6.00181L8.26578 5.87381L8.46667 5.65159C9.36089 4.65959 9.58044 3.28892 9.04 2.0747C8.49778 0.855146 7.33245 0.0969238 6 0.0969238C4.66667 0.0969238 3.50222 0.855146 2.96 2.0747C2.41956 3.28892 2.63911 4.65959 3.53333 5.65159L3.73422 5.87381L3.46311 6.00181C1.35911 6.98848 0 9.11915 0 11.4303C0 11.7974 0.298668 12.0969 0.666664 12.0969H7.77778C8.14578 12.0969 8.44445 11.7974 8.44445 11.4303C8.44445 11.0631 8.14578 10.7636 7.77778 10.7636H1.37245L1.42667 10.4969C1.86844 8.33426 3.79111 6.76359 6 6.76359C8.57333 6.76359 10.6667 8.85693 10.6667 11.4303C10.6667 11.7974 10.9653 12.0969 11.3333 12.0969C11.7013 12.0969 12 11.7974 12 11.4303C12 9.11915 10.6409 6.98848 8.53689 6.00181ZM6 5.43026C4.89689 5.43026 4 4.53248 4 3.43026C4 2.32803 4.89689 1.43026 6 1.43026C7.10311 1.43026 8 2.32803 8 3.43026C8 4.53248 7.10311 5.43026 6 5.43026Z" fill="currentColor"/>
                            </svg>
                            <span>2 Adults, 2 Children</span>
                        </div>

                        <div class="hidden h-5 w-px bg-gray-300 sm:block"></div> <!-- thin divider like Figma -->

                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Tue, 1 Apr 2025 - Sat, 5 Apr 2025</span>
                        </div>
                    </div>
                </div>

                <!-- Right: CTA (vertically centered to the left block) -->
                <button
                    wire:click="viewFullItinerary"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl">
                    View full itinerary
                </button>
            </div>
        </div>






        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            <div class="self-start">
                <div class="rounded-2xl border border-gray-200 bg-white dark:bg-gray-800 shadow-sm">
                    <!-- Title -->
                    <h2 class="text-2xl font-semibold tracking-tight flex items-center gap-2 p-6 pb-0">
                        Your trip has been booked <span>🎉</span>
                    </h2>

                    <!-- inset divider under title -->
                    <div class="mt-4 h-px bg-gray-200 mx-6"></div>

                    <!-- Booking reference -->
                    <div class="py-5 px-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                <path d="M19 5H5c-1.105 0-2 .895-2 2v10c0 1.105.895 2 2 2h14c1.105 0 2-.895 2-2V7c0-1.105-.895-2-2-2Z"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 7l9 6 9-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="font-medium text-gray-900">Booking reference</span>
                        </div>
                        <span class="text-gray-900">{{ $bookingReference }}</span>
                    </div>

                    <!-- Order date -->
                    <div class="py-5 px-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                <path d="M19 5H5c-1.105 0-2 .895-2 2v10c0 1.105.895 2 2 2h14c1.105 0 2-.895 2-2V7c0-1.105-.895-2-2-2Z"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="font-medium text-gray-900">Order date</span>
                        </div>
                        <span class="text-gray-900">{{ $orderDate }}</span>
                    </div>

                    <!-- Booking status -->
                    <div class="py-5 px-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="font-medium text-gray-900">Booking status</span>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-green-50 text-green-700 px-4 py-1.5 text-sm font-medium ring-1 ring-green-200">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      Confirmed
    </span>
                    </div>

                    <!-- inset divider under status -->
                    <div class="h-px bg-gray-200 mx-6"></div>

                    <!-- Print link with no extra bottom padding -->
                    <!-- Print link with no extra bottom space -->
                    <button
                        type="button"
                        wire:click="printBookingConfirmation"
                        class="px-6 py-4 inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 hover:underline w-full text-left"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
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
