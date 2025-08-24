@use(Nezasa\Checkout\Enums\Section)
@use(Nezasa\Checkout\Dtos\View\ShowTraveller)
@php($state = $isExpanded ? 'editing' : 'valid')


<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.traveller_details')}}"
    :state="$state"
    :showEdit="$state === 'valid'"
    :showCheck="$isCompleted"
    onEdit="expand('{{Section::Traveller->value}}')"
>
    <form wire:submit="save">

        @foreach($paxInfo as $roomNumber => $room)

            <h2 class="text-xl font-semibold mb-4">{{trans('checkout::page.trip_details.room')}} {{$roomNumber + 1}}</h2>

        @php($showThis = collect($paxInfo[$roomNumber])->pluck('showTraveller')->filter(fn(ShowTraveller $item) => $item->isShowing)->isEmpty())
        @if($showThis)
            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mt-6 mb-6"></div>
        @endif

        <div id="room-{{$roomNumber}}" @if($showThis) class="hidden" @endif>
            <div class="max-w-6xl mx-auto">
                <!-- Title -->



                <div class="mb-3">
                    <div role="tablist" aria-label="Travellers"
                         class="relative flex gap-6 border-b border-gray-200">
                        @foreach($room as $i => ['showTraveller' => $trav])
                            <button
                                type="button"
                                role="tab"
                                id="tab-{{ $roomNumber }}-{{ $i }}"
                                aria-selected="{{ $trav->isShowing ? 'true' : 'false' }}"
                                aria-controls="panel-{{ $roomNumber }}-{{ $i }}"
                                @class([
                                  'group relative flex items-center gap-2 px-4 py-2 text-base leading-6 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
                                  'z-10 font-semibold text-gray-900 bg-white border border-gray-200 border-b-0 rounded-t-[10px] -mb-px
                                   after:content-[\'\'] after:absolute after:left-0 after:right-0 after:-bottom-px after:h-px after:bg-white' => $trav->isShowing,
                                  'z-0 font-medium text-gray-400 bg-transparent hover:text-gray-600' => ! $trav->isShowing,
                                ])
                            >
                                <svg class="@if($trav->isShowing) w-5 h-5 text-gray-700 @else w-5 h-5 text-gray-400 group-hover:text-gray-600 @endif"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5.121 17.804A10.97 10.97 0 0112 15c2.136 0 4.113.635 5.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>
          {{ trans('checkout::page.trip_details.traveller') }} {{ $i+1 }} –
          {{ $trav->isAdult ? trans('checkout::page.trip_details.adult') : trans('checkout::page.trip_details.child') }}
        </span>
                            </button>
                        @endforeach
                    </div>
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
                            @php($label = trans('checkout::page.trip_details.previous_traveller'))
                        @endif

                        @if($loop->first && !$loop->parent->first)
                            @php($label = trans('checkout::page.trip_details.previous_room'))
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
                                @php($label = trans('checkout::page.trip_details.next_traveller'))
                            @endif

                            @if($loop->last && !$loop->parent->last)
                                @php($label = trans('checkout::page.trip_details.next_room'))
                            @endif

                            @if($loop->last && $loop->parent->last)
                                @php($label = trans('checkout::page.trip_details.next_step'))
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
