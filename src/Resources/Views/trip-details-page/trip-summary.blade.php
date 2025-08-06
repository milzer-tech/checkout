@use(Illuminate\Support\Number)

<div class="border border-[color:var(--border)] dark:border-gray-600 bg-transparent rounded-[12px] p-4 sm:p-6 mb-6">
    {{-- Header with image and title --}}
    <div class="flex items-center gap-4 mb-6">
        <div class="w-[60px] h-[60px] rounded-lg overflow-hidden">
            <img
                src="/images/hotel-preview.png"
                alt="Palma destination"
                class="w-full h-full object-cover"
            />
        </div>
        <div class="flex-1">
            <h2 class="font-mulish font-bold text-[18px] leading-[28px] tracking-[0%] align-middle mb-2 dark:text-white">{{str($itinerary->title)->limit(40) }}</h2>
            <div class="flex justify-end">
                <button
                    class="text-blue-600 font-medium text-sm hover:underline focus:outline-none bg-transparent px-2 py-1 rounded transition flex items-center gap-1.5">
                    <svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4">
                        <path
                            d="M12.1742 3.77587C8.07578 2.1367 8.38366 2.24805 8.27228 2.2518C8.06116 2.2593 7.91003 2.43851 7.91003 2.62448L7.91003 9.86916L4.91003 11.0689L4.91003 4.41249C4.91003 4.25203 4.80841 4.11743 4.66628 4.06419C4.68128 3.90335 4.59616 3.74513 4.43791 3.68214L0.924159 2.27617C0.683034 2.17869 0.410034 2.35528 0.410034 2.62448L0.410034 7.12358C0.410034 7.33053 0.577659 7.49851 0.785034 7.49851C0.992409 7.49851 1.16003 7.33053 1.16003 7.12358L1.16003 3.17824C4.48253 4.50772 4.05016 4.33563 4.16678 4.37988C4.15628 4.4931 4.16003 5.34231 4.16003 11.0689L1.16003 9.86916L1.16003 8.62328C1.16003 8.41595 0.992409 8.24835 0.785034 8.24835C0.577659 8.24835 0.410034 8.41595 0.410034 8.62328L0.410034 10.123C0.410034 10.2763 0.503409 10.4139 0.645909 10.4709C3.84241 11.7494 4.29653 11.9327 4.40941 11.9732C4.60178 12.0422 4.35916 12.0966 8.28503 10.5268C12.2454 12.1105 11.9315 11.9976 12.035 11.9976C12.2428 11.9976 12.41 11.8285 12.41 11.6227L12.41 4.12418C12.41 3.97046 12.3167 3.83286 12.1742 3.77587ZM11.66 11.0689L8.66003 9.86916L8.66003 3.17787L11.66 4.37763L11.66 11.0689Z"
                            fill="currentColor"/>
                        <path
                            d="M5.75603 6.62418C5.51566 6.35648 3.41003 3.97158 3.41003 2.62448C3.41003 1.17764 4.58791 0 6.03504 0C7.48216 0 8.66004 1.17764 8.66004 2.62448C8.66004 3.97158 6.55441 6.35648 6.31403 6.62418C6.16478 6.79027 5.90491 6.7899 5.75603 6.62418ZM6.03504 0.74985C5.00116 0.74985 4.16003 1.59081 4.16003 2.62448C4.16003 3.33983 5.21603 4.83091 6.03504 5.80197C6.85403 4.83054 7.91003 3.33908 7.91003 2.62448C7.91003 1.59081 7.06891 0.74985 6.03504 0.74985Z"
                            fill="currentColor"/>
                        <path
                            d="M6.03503 3.74925C5.41478 3.74925 4.91003 3.2446 4.91003 2.62447C4.91003 2.00434 5.41478 1.49969 6.03503 1.49969C6.65528 1.49969 7.16003 2.00434 7.16003 2.62447C7.16003 3.2446 6.65528 3.74925 6.03503 3.74925ZM6.03503 2.24955C5.82803 2.24955 5.66003 2.41751 5.66003 2.62447C5.66003 2.83143 5.82803 2.99939 6.03503 2.99939C6.24203 2.99939 6.41003 2.83143 6.41003 2.62447C6.41003 2.41751 6.24203 2.24955 6.03503 2.24955Z"
                            fill="currentColor"/>
                    </svg>
                    <span><a href="{{config('checkout.nezasa.base_url')}}/itineraries/{{$this->itineraryId}}">View full itinerary</a></span>
                </button>
            </div>
        </div>
    </div>

    <hr class="border-[#E0E2E8] dark:border-gray-600 my-4"/>

    {{-- Travel date section --}}
    <div class="mb-5">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 mr-2 text-[#333743] dark:text-gray-300" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
            <h3 class="text-base font-bold text-[rgba(37,42,49,1)] dark:text-white leading-6">Travel date</h3>
        </div>
        <div class="ml-7">
            <p class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$itinerary->startDate->format('D, j M Y')}}
                - {{$itinerary->endDate->format('D, j M Y')}}</p>
            <p class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{$itinerary->nights}}
                nights</p>
        </div>
    </div>

    {{-- Travelers section --}}
    <div class="mb-5">
        <div class="flex items-center mb-2">
            <svg width="20" height="20" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5 mr-2 text-[#333743] dark:text-gray-300">
                <path
                    d="M8.53689 6.00181L8.26578 5.87381L8.46667 5.65159C9.36089 4.65959 9.58044 3.28892 9.04 2.0747C8.49778 0.855146 7.33245 0.0969238 6 0.0969238C4.66667 0.0969238 3.50222 0.855146 2.96 2.0747C2.41956 3.28892 2.63911 4.65959 3.53333 5.65159L3.73422 5.87381L3.46311 6.00181C1.35911 6.98848 0 9.11915 0 11.4303C0 11.7974 0.298668 12.0969 0.666664 12.0969H7.77778C8.14578 12.0969 8.44445 11.7974 8.44445 11.4303C8.44445 11.0631 8.14578 10.7636 7.77778 10.7636H1.37245L1.42667 10.4969C1.86844 8.33426 3.79111 6.76359 6 6.76359C8.57333 6.76359 10.6667 8.85693 10.6667 11.4303C10.6667 11.7974 10.9653 12.0969 11.3333 12.0969C11.7013 12.0969 12 11.7974 12 11.4303C12 9.11915 10.6409 6.98848 8.53689 6.00181ZM6 5.43026C4.89689 5.43026 4 4.53248 4 3.43026C4 2.32803 4.89689 1.43026 6 1.43026C7.10311 1.43026 8 2.32803 8 3.43026C8 4.53248 7.10311 5.43026 6 5.43026Z"
                    fill="currentColor"/>
            </svg>
            <h3 class="text-base font-bold text-[rgba(37,42,49,1)] dark:text-white leading-6">Travellers</h3>
        </div>
        <div class="ml-7">
            <p class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">
                @php
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
                @endphp
            </p>
        </div>
    </div>

    {{-- Booking details collapsible section --}}
    <div class="mb-5">
        <button wire:click="$toggle('isExpanded')"
                class="text-blue-600 font-medium text-sm hover:underline focus:outline-none bg-transparent px-2 py-1 rounded transition flex items-center gap-1.5">
            <span>Booking details</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        @if($isExpanded)
            <div class="mt-5 space-y-6">

                @if($itinerary->stays->isNotEmpty())
                    {{-- Stay section --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold dark:text-white">Stay</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 bg-[#F2FCE2] dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm rounded-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            Available
                        </span>
                        </div>

                        @foreach($itinerary->stays as $stay)
                            <div class="flex items-start">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span
                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->name }}</span>
                                </div>
                                <div class="ml-auto text-right">
                                    <div
                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $stay->checkIn->format('D, j M') }}
                                        - {{$stay->checkOut->format('D, j M')}}</div>
                                    <div
                                        class="text-base font-normal leading-6 text-[rgba(128,128,128,1)] dark:text-gray-400">{{ $stay->nights }}
                                        nights
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
                            <h3 class="font-semibold dark:text-white">Activities</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 bg-[#F2FCE2] dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm rounded-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            Available
                        </span>
                        </div>

                        @foreach($itinerary->activities as $activity)
                            <div class="flex items-start">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span
                                        class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->name }}</span>
                                </div>
                                <div class="ml-auto text-right">
                                    <div class="text-base font-normal leading-6 text-[rgba(51,55,67,1)] dark:text-gray-200">{{ $activity->startDateTime->format('D, j M') }}</div>
                                </div>
                            </div>
                        @endforeach


                    </div>
                @endif

                @if($itinerary->hasFlights())
                    {{-- Flights section --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold dark:text-white">Flights</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 bg-[#F2FCE2] dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm rounded-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            Available
                        </span>
                        </div>
                        <div class="space-y-2">
                            @foreach($itinerary->flights as $flight)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"></path>
                                        </svg>
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
                            <h3 class="font-semibold dark:text-white">Transfers</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 bg-[#F2FCE2] dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm rounded-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            Available
                        </span>
                        </div>
                        <div class="space-y-2">
                            @foreach($itinerary->transfers as $transfer)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"></path>
                                        </svg>
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
                            <h3 class="font-semibold dark:text-white">Rental cars</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 bg-[#F2FCE2] dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm rounded-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            Available
                        </span>
                        </div>
                        <div class="space-y-2">
                            @foreach($itinerary->rentalCars as $rentalCar)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"></path>
                                        </svg>
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
            </div>

        @endif
    </div>

    {{-- Divider --}}
    <hr class="border-gray-200 dark:border-gray-600 my-4"/>


    @if($itinerary->promoCodeResponse->promoCode)
    {{-- Discount row --}}
    <div class="flex justify-between items-center text-sm text-gray-700 dark:text-gray-300 mb-2">
        <div class="flex items-center gap-1">
            {{-- Correct icon (smaller) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>

            <span class="font-semibold">{{$itinerary->promoCodeResponse->promoCode->code}}</span>
            <span class="font-semibold">(-{{$itinerary->promoCodeResponse->decreasePercent()}}%)</span>
        </div>
        <span class="font-semibold dark:text-white">
        -{{Number::currency($itinerary->promoCodeResponse->decreaseAmount(), $itinerary->price->currency)}}
    </span>
    </div>
    @endif

    {{-- Total price section --}}
    <div>
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl dark:text-white">Total ({{strtoupper($itinerary->price->currency)}})</h3>


            <span wire:loading.remove class="text-2xl font-bold dark:text-white">{{ Number::currency($itinerary->price->amount, $itinerary->price->currency) }}</span>

            <svg wire:loading class="animate-spin h-4 w-4 text-blue-500"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>



        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Includes all taxes, fees, surcharges, and Tripbuilder service fees. Tripbuilder service
            fees are calculated per passenger and are not refundable.
        </p>
    </div>

</div>
