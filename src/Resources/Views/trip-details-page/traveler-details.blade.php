@use(Nezasa\Checkout\Enums\Section)
@use(Nezasa\Checkout\Dtos\View\ShowTraveller)
@php($state = $isExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="Traveller details"
    :state="$state"
    :showEdit="$state === 'valid'"
    :showCheck="$isCompleted"
    onEdit="expand('{{Section::Traveller->value}}')"
>
    <form wire:submit="save">

        @foreach($paxInfo as $roomNumber => $room)

            <h2 class="text-xl font-semibold mb-4">Room {{$roomNumber + 1}}</h2>

        @php($showThis = collect($paxInfo[$roomNumber])->pluck('showTraveller')->filter(fn(ShowTraveller $item) => $item->isShowing)->isEmpty())
        @if($showThis)
            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mt-6 mb-6"></div>
        @endif

        <div id="room-{{$roomNumber}}" @if($showThis) class="hidden" @endif>
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
                <div @if(! $showTraveller->isShowing) class="hidden" @endif>
                    @include('checkout::trip-details-page.inputs',[
                             'requirements' => $passengerRequirements,
                             'countryCodes' => $countryCodes,
                             'countriesResponse' => $countriesResponse,
                             'saveTo' => "paxInfo.$roomNumber.$i"
                         ])
                    <div class="flex justify-between items-center mt-8">
                        @if(!$loop->first)
                            @php($label = "Previous traveller")
                        @endif

                        @if($loop->first && !$loop->parent->first)
                            @php($label = "Previous room")
                        @endif

                        <button type="button" wire:click="showPreviousTraveller('{{$roomNumber}}-{{$i}}')"
                        class="inline-flex items-center px-5 py-2 rounded-lg border border-gray-200 bg-white text-blue-600 hover:bg-gray-50 shadow-sm {{ isset($label) ? '' : 'invisible' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <span class="font-medium">{{$label ?? ''}}</span>
                        </button>

                            @if(!$loop->last)
                                @php($label = "Next traveller")
                            @endif

                            @if($loop->last && !$loop->parent->last)
                                @php($label = "Next room")
                            @endif

                            @if($loop->last && $loop->parent->last)
                                @php($label = "Next step")
                            @endif

                            <button type="button" wire:click="showNextTraveller('{{$roomNumber}}-{{$i}}')" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">{{$label}}</button>
                    </div>
                </div>
            @endforeach

            @unless($loop->last)
                <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mt-6 mb-6"></div>
            @endunless
        </div>
        @endforeach
    </form>
</x-checkout::editable-box>
