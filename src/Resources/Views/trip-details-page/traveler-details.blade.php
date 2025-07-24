@php($state = $travelerExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="Traveller details"
    :state="$state"
    :showEdit="$state === 'valid'"
    :showCheck="$isCompleted"
    onEdit="editTraveler"
>
    <form wire:submit="save">

        @foreach($paxInfo as $roomNumber => $room)

            <h2 class="text-xl font-semibold mb-4">Room {{$roomNumber + 1}}</h2>

        <div id="room-{{$roomNumber}}" @if($roomNumber !== 0) class="hidden" @endif>
            <div class="max-w-6xl mx-auto">
                <!-- Title -->


                <!-- Traveller Tabs Row -->
                <div class="relative flex border-b border-gray-200 mb-3">

                    @foreach($room as $i => ['showTraveller' => $showTraveller])
                        <!-- Traveller 1 - Active -->
                        <button type="button" id="{{$roomNumber}}-{{$i}}" @if($showTraveller->isShowing)
                            class="relative z-10 flex items-center space-x-2 text-sm font-semibold text-gray-900 bg-white border-t border-l border-r border-gray-300 rounded-t-md py-2"
                                @else
                                    class="relative z-0 flex items-center space-x-2 text-sm font-medium text-gray-400 bg-white px-4 py-2"
                            @endif
                        >
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5.121 17.804A10.97 10.97 0 0112 15c2.136 0 4.113.635 5.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Traveller {{$i+1}} - @if($showTraveller->isAdult)
                                    Adult
                                @else
                                    Child
                                @endif</span>
                        </button>
                    @endforeach

                    <!-- underline under whole row -->
                    <div class="absolute bottom-0 left-0 w-full h-px bg-gray-200 z-0"></div>

                </div>
            </div>

            @foreach($room as $i => ['showTraveller' =>$showTraveller])
                {{-- Traveller details form --}}


                <div @if(! $showTraveller->isShowing) class="hidden" @endif>


                    @include('checkout::trip-details-page.inputs',[
                             'requirements' => $passengerRequirements,
                             'countryCodes' => $countryCodes,
                             'countriesResponse' => $countriesResponse,
                             'saveTo' => "paxInfo.$roomNumber.$i"
                         ])

{{--                    @unless($loop->last)--}}
                        <div class="flex justify-end mt-8">
                            <button type="button" wire:click="showNextTraveller('{{"$roomNumber-$i" }}')"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                                Next traveller
                            </button>
                        </div>
{{--                    @endunless--}}


                </div>

            @endforeach

            @unless($loop->last)
                {{-- Divider between rooms --}}
                <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mt-6 mb-6"></div>
            @endunless
        </div>
        @endforeach


    </form>
</x-checkout::editable-box>
