@php use Nezasa\Checkout\Dtos\Planner\Entities\ItineraryStay;use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum; @endphp
<div class="flex flex-col min-h-screen">
    <div class="flex-grow space-y-6">
        <!-- Page header - removed the image from here -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold flex items-center gap-2">
                {{trans('checkout::page.booking_confirmation.booking_confirmation')}} <span class="text-2xl">@if($output->bookingStatusEnum->isCompleteSuccess())
                        🎉
                    @endif</span>
            </h1>

            @if($output->bookingStatusEnum->isCompleteSuccess())
                <div class="bg-green-100 text-green-700 px-3 py-1 rounded-full flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{trans('checkout::page.booking_confirmation.confirmed')}}
                </div>
            @elseif($output->bookingStatusEnum->isPartialFailure())

                <span
                    class="inline-flex items-center gap-1 rounded-full bg-[#E8F1FF] text-gray-900 px-4 py-1.5 text-sm font-medium whitespace-nowrap">

                     <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4v8" stroke="currentColor" stroke-width="3"
                                                  stroke-linecap="round"></path>
                                            <circle cx="12" cy="17" r="2" fill="currentColor"></circle>
                                        </svg>
                    {{trans('checkout::page.booking_confirmation.pending')}}
                </span>
            @else
                <span
                    class="inline-flex items-center px-3 py-1 bg-[#FEE2E2] dark:bg-red-900/30 text-black text-sm rounded-full">
    <svg class="w-4 h-4 mr-2 text-red-500 dark:text-red-400" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" stroke="currentColor"/>
    </svg>
    {{trans('checkout::page.booking_confirmation.not_booked')}}
</span>
            @endif

        </div>


        <!-- ONE card for image + content (matches Figma) -->
        <div class="border rounded-lg overflow-hidden border-gray-200 bg-white dark:bg-gray-800 shadow-sm">

            <!-- Content -->
            <div class="p-6 border-t border-gray-100 flex justify-between items-center">
                <!-- Left: title + details -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">{{str($itinerary->title)->limit(40) }}</h2>

                    <div class="flex flex-wrap items-center gap-6 text-gray-700 dark:text-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 12 13" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M8.53689 6.00181L8.26578 5.87381L8.46667 5.65159C9.36089 4.65959 9.58044 3.28892 9.04 2.0747C8.49778 0.855146 7.33245 0.0969238 6 0.0969238C4.66667 0.0969238 3.50222 0.855146 2.96 2.0747C2.41956 3.28892 2.63911 4.65959 3.53333 5.65159L3.73422 5.87381L3.46311 6.00181C1.35911 6.98848 0 9.11915 0 11.4303C0 11.7974 0.298668 12.0969 0.666664 12.0969H7.77778C8.14578 12.0969 8.44445 11.7974 8.44445 11.4303C8.44445 11.0631 8.14578 10.7636 7.77778 10.7636H1.37245L1.42667 10.4969C1.86844 8.33426 3.79111 6.76359 6 6.76359C8.57333 6.76359 10.6667 8.85693 10.6667 11.4303C10.6667 11.7974 10.9653 12.0969 11.3333 12.0969C11.7013 12.0969 12 11.7974 12 11.4303C12 9.11915 10.6409 6.98848 8.53689 6.00181ZM6 5.43026C4.89689 5.43026 4 4.53248 4 3.43026C4 2.32803 4.89689 1.43026 6 1.43026C7.10311 1.43026 8 2.32803 8 3.43026C8 4.53248 7.10311 5.43026 6 5.43026Z"
                                    fill="currentColor"/>
                            </svg>
                            <span>@php
                                    $str = str($itinerary->adults)
                                    ->append(' ')
                                    ->append(str('Adult')->plural($itinerary->adults));

                                    if($itinerary->childrenAges->isNotEmpty()){
                                       $str = $str->append(', ' . $itinerary->children. ' ')
                                       ->append(str('Child')->plural($itinerary->children))
                                       ->append(' (')
                                       ->append(
                                            $itinerary->childrenAges->map(function ( $age) {
                                                return  $age . ' ' . str('year')->plural($age) . ' old';
                                            })->implode(', ')
                                        )
                                        ->append(')');
                                    }

                                    echo $str;
                                @endphp</span>
                        </div>

                        <div class="hidden h-5 w-px bg-gray-300 sm:block"></div> <!-- thin divider like Figma -->

                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                                <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                                <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span>{{$itinerary->startDate->format('D, j M Y')}} - {{$itinerary->endDate->format('D, j M Y')}}</span>
                        </div>
                    </div>
                </div>

                <!-- Right: CTA (vertically centered to the left block) -->
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl">
                    <a href="{{config('checkout.nezasa.base_url')}}/itineraries/{{$this->itineraryId}}/travel-summary">
                        {{trans('checkout::page.trip_details.view_full_itinerary')}}
                    </a>
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            <!-- LEFT: Your trip has been booked -->
            <div class="self-start">
                <div class="p-6 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 shadow-sm">

                    <!-- Title -->
                    <h2 class="text-2xl font-semibold tracking-tight flex items-center gap-2 mb-4">
                        @if($output->bookingStatusEnum->isCompleteSuccess())
                            {{trans('checkout::page.booking_confirmation.your_trip_has_been_booked')}} <span>🎉</span>
                        @else
                            {{trans('checkout::page.booking_confirmation.your_trip_could_not_be_booked')}}
                        @endif
                    </h2>

                    <!-- divider under title (inset by padding) -->
                    <div class="h-px bg-gray-200 mb-2"></div>

                    <!-- Booking reference -->
                    <div class="py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M19 5H5c-1.105 0-2 .895-2 2v10c0 1.105.895 2 2 2h14c1.105 0 2-.895 2-2V7c0-1.105-.895-2-2-2Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                                <path d="M3 7l9 6 9-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span
                                class="font-medium text-gray-900"><b>{{trans('checkout::page.booking_confirmation.booking_reference')}}</b></span>
                        </div>
                        <span class="text-gray-900">{{$output?->bookingReference}}</span>
                    </div>


                    <!-- Order date -->
                    <div class="py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M19 5H5c-1.105 0-2 .895-2 2v10c0 1.105.895 2 2 2h14c1.105 0 2-.895 2-2V7c0-1.105-.895-2-2-2Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                                <path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span
                                class="font-medium text-gray-900"><b>{{trans('checkout::page.booking_confirmation.order_date')}}</b></span>
                        </div>
                        <span class="text-gray-900">{{$output->orderDate?->format('D, j M Y')}}</span>
                    </div>

                    @if(! $output->isPaymentSuccessful)
                        <div class="py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2"
                                          stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2"
                                          stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span
                                    class="font-medium text-gray-900"><b>{{trans('checkout::page.booking_confirmation.payment_result')}}</b></span>
                            </div>

                            <!--[if BLOCK]><![endif]--> <span
                                class="inline-flex items-center px-3 py-1 bg-[#FEE2E2] dark:bg-red-900/30 text-black text-sm rounded-full">
    <svg class="w-4 h-4 mr-2 text-red-500 dark:text-red-400" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
              stroke="currentColor"></path>
    </svg>
    Failed
</span>
                            <!--[if ENDBLOCK]><![endif]-->
                        </div>

                    @endif

                    <!-- Booking status -->
                    <div class="py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span
                                class="font-medium text-gray-900"><b>{{trans('checkout::page.booking_confirmation.booking_status')}}</b></span>
                        </div>

                        @if($output->bookingStatusEnum->isCompleteSuccess())
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-green-50 text-green-700 px-4 py-1.5 text-sm font-medium ring-1 ring-green-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{trans('checkout::page.booking_confirmation.confirmed')}}
        </span>
                        @elseif($output->bookingStatusEnum->isPartialFailure())

                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-[#E8F1FF] text-gray-900 px-4 py-1.5 text-sm font-medium whitespace-nowrap">

                     <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4v8" stroke="currentColor" stroke-width="3"
                                                  stroke-linecap="round"></path>
                                            <circle cx="12" cy="17" r="2" fill="currentColor"></circle>
                                        </svg>
                    {{trans('checkout::page.booking_confirmation.pending')}}
                </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 bg-[#FEE2E2] dark:bg-red-900/30 text-black text-sm rounded-full">
    <svg class="w-4 h-4 mr-2 text-red-500 dark:text-red-400" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" stroke="currentColor"/>
    </svg>
    {{trans('checkout::page.booking_confirmation.not_booked')}}
</span>
                        @endif

                    </div>

                    <!-- divider under status -->
                    <div class="h-px bg-gray-200"></div>

                    <!-- Print link -->
                    <button
                        type="button"
                        class="py-4 inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 hover:underline w-full text-left"
                    >


                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <a href="{{config('checkout.nezasa.base_url')}}/checkouts/{{$checkoutId}}">
                            @if($output->bookingStatusEnum->isCompleteSuccess())
                                {{trans('checkout::page.booking_confirmation.print_booking_confirmation')}}
                            @else
                                {{trans('checkout::page.booking_confirmation.print_booking_proposal')}}
                            @endif
                        </a>
                    </button>

                </div>



                                @if($output->bookingStatusEnum->isPartialFailure())
                    {{-- Services without confirmation (Figma) --}}
                    <div class="mt-6 p-6 border border-[#2681FF] rounded-xl bg-white shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{trans('checkout::page.booking_confirmation.services_without_confirmation')}}
                        </h3>

                        <div class="mt-4 h-px bg-gray-200"></div>

                        <p class="mt-4 text-l text-gray-900 leading-6">
                            {{trans('checkout::page.booking_confirmation.services_require_manual_confirmation')}}
                        </p>

                        {{-- Rental cars block (repeat this whole block per rental car) --}}
                        <div class="mt-6 space-y-6">

                            @if($itinerary->getUnconfirmedStays()->isNotEmpty())
                                {{-- Stay section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.stay')}}</h3>
                                    </div>

                                    @foreach($itinerary->getUnconfirmedStays() as $stay)
                                        <div class="flex items-start mb-1">
                                            <div class="flex items-center">
                                                @include('checkout::components.availability-status', ['availability' => $stay->availability])
                                                <span
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->name }}</span>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->checkIn->format('D, j M') }}
                                                    - {{$stay->checkOut->format('D, j M')}}</div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            @if($itinerary->getUnconfirmedActivities()->isNotEmpty())
                                {{-- Stay section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.activities')}}</h3>
                                    </div>

                                    @foreach($itinerary->getUnconfirmedActivities() as $activity)
                                        <div class="flex items-start">
                                            <div class="flex items-center">
                                                @include('checkout::components.availability-status', ['availability' => $activity->availability])
                                                <span
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->name }}</span>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->startDateTime->format('D, j M') }}</div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            @if($itinerary->getUnconfirmedFlights()->isNotEmpty())
                                {{-- Flights section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.flights')}}</h3>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->getUnconfirmedFlights() as $flight)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $flight->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$flight->getTitle()}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$flight->startDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($itinerary->getUnconfirmedTransfers()->isNotEmpty())
                                {{-- Transfers section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.transfers')}}</h3>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->getUnconfirmedTransfers() as $transfer)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $transfer->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$transfer->getTitle()}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$transfer->startDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                            @if($itinerary->getUnconfirmedRentalCars()->isNotEmpty())
                                {{-- renal car section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.rental_cars')}}</h3>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->getUnconfirmedRentalCars() as $rentalCar)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $rentalCar->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$rentalCar->name}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$rentalCar->startDateTime->format('D, j M')}}
                                                    - {{$rentalCar->endDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($itinerary->getUnconfirmedUpsellItems()->isNotEmpty())
                                {{-- renal car section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.upsell_items')}}</h3>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->getUnconfirmedUpsellItems() as $item)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $item->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$item->name}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>


                        <div class="mt-6 h-px bg-gray-200"></div>

                        <button
                            type="button"
                            wire:click="contactSupport"
                            class="mt-5 inline-flex items-center gap-2 text-[#2681FF] font-medium text-sm hover:underline"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a8 8 0 01-8 8H9l-4 3v-3.5A6.5 6.5 0 013.5 12a8.5 8.5 0 018.5-8.5h1A8 8 0 0121 12Z"/>
                            </svg>
                            Contact support
                        </button>
                    </div>
                @endif

                {{--                ////--}}



                @if($output->bookingStatusEnum->isCompleteFailed())
                    {{-- Services that could not be booked (Failed version) --}}
                    <div class="mt-6 p-6 border border-red-400 rounded-xl bg-white shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ trans('checkout::page.booking_confirmation.services_that_could_not_be_booked') }}
                        </h3>

                        <div class="mt-4 h-px bg-gray-200"></div>

                        <div class="mt-6 space-y-6">

                            {{-- Rental car block --}}

                            @if($itinerary->stays->isNotEmpty())
                                {{-- Stay section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.stay')}}</h3>
                                        @include('checkout::components.available',['availability' => AvailabilityEnum::None])
                                    </div>

                                    @foreach($itinerary->stays as $stay)
                                        <div class="flex items-start">
                                            <div class="flex items-center mb-1">
                                                @include('checkout::components.availability-status', ['availability' => AvailabilityEnum::None])
                                                <span
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->name }}</span>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->checkIn->format('D, j M') }}
                                                    - {{$stay->checkOut->format('D, j M')}}</div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            @if($itinerary->activities->isNotEmpty())
                                {{-- Stay section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.activities')}}</h3>
                                        @include('checkout::components.available',['availability' => AvailabilityEnum::None])
                                    </div>

                                    @foreach($itinerary->activities as $activity)
                                        <div class="flex items-start">
                                            <div class="flex items-center">
                                                @include('checkout::components.availability-status', ['availability' => AvailabilityEnum::None])
                                                <span
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->name }}</span>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->startDateTime->format('D, j M') }}</div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            @if($itinerary->hasFlights())
                                {{-- Flights section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.flights')}}</h3>
                                        @include('checkout::components.available',['availability' => AvailabilityEnum::None])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->flights as $flight)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => AvailabilityEnum::None])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$flight->getTitle()}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$flight->startDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($itinerary->hasTransfers())
                                {{-- Transfers section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.transfers')}}</h3>
                                        @include('checkout::components.available',['availability' => AvailabilityEnum::None])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->transfers as $transfer)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => AvailabilityEnum::None])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$transfer->getTitle()}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$transfer->startDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                            @if($itinerary->hasRentalCar())
                                {{-- renal car section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.rental_cars')}}</h3>
                                        @include('checkout::components.available',['availability' => AvailabilityEnum::None])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->rentalCars as $rentalCar)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => AvailabilityEnum::None])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$rentalCar->name}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$rentalCar->startDateTime->format('D, j M')}}
                                                    - {{$rentalCar->endDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($itinerary->hasUpsellItem())
                                {{-- renal car section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.upsell_items')}}</h3>
                                        @include('checkout::components.available',['availability' => AvailabilityEnum::None])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->upsellItems as $item)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => AvailabilityEnum::None])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$item->name}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{--                                //--}}

                            {{--//--}}


                        </div>

                        <div class="mt-6 h-px bg-gray-200"></div>

                        <p class="mt-6 text-gray-900 text-l leading-6">
                            {{ trans('checkout::page.booking_confirmation.you_have_not_been_charged_for_this_booking') }}
                            {{ trans('checkout::page.booking_confirmation.we_recommend_try_again') }}
                        </p>

                        <div class="mt-6">
                            <button
                                type="button"
                                wire:click="goBackToPlanner"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl w-full">
                                {{ trans('checkout::page.booking_confirmation.go_back_to_planner') }}
                            </button>
                        </div>
                    </div>

                @endif
                {{--                ///////--}}

            </div>


            <div class="p-6 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                <h2 class="text-xl font-bold">{{trans('checkout::page.booking_confirmation.traveller_information')}}</h2>

                <!-- divider under title -->
                <div class="mt-4 h-px bg-gray-200"></div>

                <!-- Travel date (NO divider after this block) -->
                <div class="pt-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M19 4H5c-1.105 0-2 .895-2 2v14c0 1.105.895 2 2 2h14c1.105 0 2-.895 2-2V6c0-1.105-.895-2-2-2Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"/>
                            <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span
                            class="font-medium text-gray-700 dark:text-gray-200"><b>{{trans('checkout::page.trip_details.travel_date')}}</b></span>
                    </div>
                    <div class="pl-7 space-y-1">
                        <p class="text-gray-700 dark:text-gray-200">{{$itinerary->startDate->format('D, j M Y')}}
                            - {{$itinerary->endDate->format('D, j M Y')}}</p>
                        <p class="text-gray-700 dark:text-gray-200">{{$itinerary->nights}} nights</p>
                    </div>
                </div>


                <!-- Travellers -->
                <div>
                    <div class="flex items-center gap-2 mb-2 mt-6">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                            <path d="M5.5 7a3.5 3.5 0 1 0 7 0a3.5 3.5 0 0 0-7 0Z" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 21a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                        <span
                            class="font-medium text-gray-700 dark:text-gray-200"><b>{{trans('checkout::page.trip_details.travellers')}}</b></span>
                    </div>
                    <div class="pl-7">
                        @foreach($travelers as $traveler)
                            <p class="text-gray-700 dark:text-gray-200">{{ $traveler }}</p>
                        @endforeach
                    </div>
                </div>


                {{-- Booking details collapsible section --}}
                <div class="mb-5 mt-6">
                    <button wire:click="$toggle('isExpanded')"
                            class="text-blue-600 font-medium text-sm hover:underline focus:outline-none bg-transparent px-2 py-1 rounded transition flex items-center gap-1.5">
                        <span>{{trans('checkout::page.trip_details.booking_details')}}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    @if($isExpanded)
                        <div class="mt-5 space-y-6">

                            @if($itinerary->stays->isNotEmpty())
                                {{-- Stay section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.stay')}}</h3>
                                        @include('checkout::components.available',['availability' => $itinerary->getHotelsGroupStatus()])
                                    </div>

                                    @foreach($itinerary->stays as $stay)
                                        <div class="flex items-start">
                                            <div class="flex items-center">
                                                @include('checkout::components.availability-status', ['availability' => $stay->availability])
                                                <span
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->name }}</span>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->checkIn->format('D, j M') }}
                                                    - {{$stay->checkOut->format('D, j M')}}</div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(128,128,128,1)] dark:text-gray-400">{{ $stay->nights }}
                                                    {{trans('checkout::page.trip_details.night')}}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            @if($itinerary->activities->isNotEmpty())
                                {{-- Stay section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.activities')}}</h3>
                                        @include('checkout::components.available',['availability' => $itinerary->getActivitiesGroupStatus()])
                                    </div>

                                    @foreach($itinerary->activities as $activity)
                                        <div class="flex items-start">
                                            <div class="flex items-center">
                                                @include('checkout::components.availability-status', ['availability' => $activity->availability])
                                                <span
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->name }}</span>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->startDateTime->format('D, j M') }}</div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            @if($itinerary->hasFlights())
                                {{-- Flights section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.flights')}}</h3>
                                        @include('checkout::components.available',['availability' => $itinerary->getFlightsGroupStatus()])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->flights as $flight)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $flight->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$flight->getTitle()}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$flight->startDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($itinerary->hasTransfers())
                                {{-- Transfers section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.transfers')}}</h3>
                                        @include('checkout::components.available',['availability' => $itinerary->getTransfersGroupStatus()])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->transfers as $transfer)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $transfer->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$transfer->getTitle()}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$transfer->startDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                            @if($itinerary->hasRentalCar())
                                {{-- renal car section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.rental_cars')}}</h3>
                                        @include('checkout::components.available',['availability' => $itinerary->rentalCarGroupStatus()])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->rentalCars as $rentalCar)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $rentalCar->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$rentalCar->name}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$rentalCar->startDateTime->format('D, j M')}}
                                                    - {{$rentalCar->endDateTime->format('D, j M')}}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($itinerary->hasUpsellItem())
                                {{-- renal car section --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold dark:text-white">{{trans('checkout::page.trip_details.upsell_items')}}</h3>
                                        @include('checkout::components.available',['availability' => $itinerary->getUpsellItemsGroupStatus()])
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($itinerary->upsellItems as $item)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @include('checkout::components.availability-status', ['availability' => $item->availability])
                                                    <span
                                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$item->name}}</span>
                                                </div>
                                                <div
                                                    class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                    @endif
                </div>

                <!-- divider BEFORE booking details -->
                <div class="my-6 h-px bg-gray-200"></div>

                <!-- Total paid + link -->
                <div class="pt-1">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-lg">Total paid (EUR)</span>
                        <span
                            class="font-bold text-lg">{{ Number::currency($itinerary->price->downPayment->amount, $itinerary->price->downPayment->currency) }}</span>
                    </div>

                    <button
                        class="invisible w-full flex items-center gap-2 pt-4 text-blue-500 hover:bg-blue-50 hover:text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cancelation policy
                    </button>
                </div>
            </div>


        </div>


        <div class="my-8 rounded-lg border bg-[#2681FF14] border-[#2681FF14]">
            <div class="px-6 py-6 md:px-10 md:py-8 flex items-center justify-between gap-6 flex-wrap">
                <div class="grid grid-cols-[auto,1fr] gap-x-4 gap-y-2 items-start">
                    <svg class="w-7 h-7 text-gray-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path
                            d="M21 12a8 8 0 01-8 8H9l-4 3v-3.5A6.5 6.5 0 013.5 12a8.5 8.5 0 018.5-8.5h1A8 8 0 0121 12Z"
                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>

                    <h3 class="text-xl md:text-2xl font-semibold text-gray-900">
                        {{trans('checkout::page.trip_details.need_help_with_your_booking')}}
                    </h3>

                    <p class="col-span-2 text-lg text-gray-700">
                        {{trans('checkout::page.trip_details.our_travel_experts_are_available')}}
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="contactSupport"
                    class="inline-flex items-center justify-center rounded-xl border px-6 py-3 md:px-8 md:py-3 font-medium
                 bg-[#2681FF14] text-[#2681FF] border-[#2681FF]
                 hover:bg-[#2681FF14] focus:outline-none focus:ring-2 focus:ring-[#2681FF33] focus:ring-offset-2">
                    {{trans('checkout::page.trip_details.contact_support')}}
                </button>
            </div>
        </div>


    </div>
</div>
