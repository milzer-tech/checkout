@php($state = $travelerExpanded ? 'editing' : 'valid')

<x-checkout::editable-box
    title="Traveller details"
    :state="$state"
    :showEdit="$state === 'valid'"
    :showCheck="$isCompleted"
    onEdit="editTraveler"
>
    <form wire:submit="save">

        @foreach($showTravellers as $roomNumber => $room)
            <div class="max-w-6xl mx-auto">
                <!-- Title -->
                <h2 class="text-xl font-semibold mb-4">Room {{$roomNumber + 1}}</h2>

                <!-- Traveller Tabs Row -->
                <div class="relative flex border-b border-gray-200 mb-3">

                    @foreach($room as $i => $showTraveller)
                        <!-- Traveller 1 - Active -->
                        <button type="button" wire:click="showTraveller('{{"$roomNumber-$i" }}')" id="{{$roomNumber}}-{{$i}}" @if($showTraveller->show)
                                    class="relative z-10 flex items-center space-x-2 text-sm font-semibold text-gray-900 bg-white border-t border-l border-r border-gray-300 rounded-t-md py-2"
                                @else
                                    class="relative z-0 flex items-center space-x-2 text-sm font-medium text-gray-400 bg-white px-4 py-2"
                            @endif
                        >
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5.121 17.804A10.97 10.97 0 0112 15c2.136 0 4.113.635 5.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Traveller {{$i+1}} - @if($showTraveller->adult)
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


            @foreach($room as $i => $traveler)
                {{-- Traveller details form --}}

                <div @unless($traveler->show) class="hidden" @endunless>


                    @include('checkout::trip-details-page.inputs',[
                             'requirements' => $passengerRequirements,
                             'countryCodes' => $countryCodes,
                             'countriesResponse' => $countriesResponse,
                             'saveTo' => "paxInfo.$roomNumber.$i"
                         ])


{{--                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 min-w-0">--}}
{{--                        <div class="space-y-2 w-full min-w-0">--}}
{{--                            <label--}}
{{--                                class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">First--}}
{{--                                name</label>--}}
{{--                            <input type="text" wire:model="travelers.0.firstName" class="form-input w-full"--}}
{{--                                   placeholder="e.g. Harry">--}}
{{--                            @error('travelers.0.firstName') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                        </div>--}}
{{--                        <div class="space-y-2 w-full min-w-0">--}}
{{--                            <label--}}
{{--                                class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Second--}}
{{--                                name</label>--}}
{{--                            <input type="text" wire:model="travelers.0.secondName" class="form-input w-full"--}}
{{--                                   placeholder="e.g. James">--}}
{{--                            @error('travelers.0.secondName') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                        </div>--}}
{{--                        <div class="space-y-2 w-full min-w-0">--}}
{{--                            <label--}}
{{--                                class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Last--}}
{{--                                name</label>--}}
{{--                            <input type="text" wire:model="travelers.0.lastName" class="form-input w-full"--}}
{{--                                   placeholder="e.g. Potter">--}}
{{--                            @error('travelers.0.lastName') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-6 mt-4 min-w-0">--}}
{{--                        <div class="flex flex-col md:flex-row gap-4 w-full">--}}
{{--                            <div class="space-y-2 w-full min-w-0">--}}
{{--                                <label--}}
{{--                                    class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Nationality</label>--}}
{{--                                <select wire:model="travelers.0.nationality" class="form-select pr-8 w-full">--}}
{{--                                    <option value="" disabled>Select</option>--}}
{{--                                    <option value="PT">Portuguese</option>--}}
{{--                                    <option value="ES">Spanish</option>--}}
{{--                                    <option value="US">American</option>--}}
{{--                                    <option value="UK">British</option>--}}
{{--                                </select>--}}
{{--                                @error('travelers.0.nationality') <span--}}
{{--                                    class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            </div>--}}
{{--                            <div class="space-y-2 w-full min-w-0">--}}
{{--                                <label--}}
{{--                                    class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Gender</label>--}}
{{--                                <select wire:model="travelers.0.gender" class="form-select pr-8 w-full">--}}
{{--                                    <option value="" disabled>Select</option>--}}
{{--                                    <option value="M">Male</option>--}}
{{--                                    <option value="F">Female</option>--}}
{{--                                    <option value="O">Other</option>--}}
{{--                                </select>--}}
{{--                                @error('travelers.0.gender') <span--}}
{{--                                    class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="space-y-2 w-full min-w-0">--}}
{{--                            <label--}}
{{--                                class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Date--}}
{{--                                of birth</label>--}}
{{--                            @include('checkout::components.date-field', [--}}
{{--                                'day' => 'travelers.0.dateOfBirthDay',--}}
{{--                                'month' => 'travelers.0.dateOfBirthMonth',--}}
{{--                                'year' => 'travelers.0.dateOfBirthYear'--}}
{{--                            ])--}}
{{--                            @error('travelers.0.dateOfBirthDay') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            @error('travelers.0.dateOfBirthMonth') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            @error('travelers.0.dateOfBirthYear') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-6 mt-4 min-w-0">--}}
{{--                        <div class="flex flex-col md:flex-row gap-4 w-full">--}}
{{--                            <div class="space-y-2 w-full min-w-0">--}}
{{--                                <label--}}
{{--                                    class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Passport--}}
{{--                                    number</label>--}}
{{--                                <input type="text" wire:model="travelers.0.passportNumber" class="form-input w-full"--}}
{{--                                       placeholder="e.g. 1234">--}}
{{--                                @error('travelers.0.passportNumber') <span--}}
{{--                                    class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            </div>--}}
{{--                            <div class="space-y-2 w-full min-w-0">--}}
{{--                                <label--}}
{{--                                    class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Passport--}}
{{--                                    issuing country</label>--}}
{{--                                <select wire:model="travelers.0.passportIssuingCountry" class="form-select pr-8 w-full">--}}
{{--                                    <option value="" disabled>Select</option>--}}
{{--                                    <option value="PT">Portugal</option>--}}
{{--                                    <option value="ES">Spain</option>--}}
{{--                                    <option value="US">United States</option>--}}
{{--                                    <option value="UK">United Kingdom</option>--}}
{{--                                </select>--}}
{{--                                @error('travelers.0.passportIssuingCountry') <span--}}
{{--                                    class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="space-y-2 w-full min-w-0">--}}
{{--                            <label--}}
{{--                                class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">Passport--}}
{{--                                expiration date</label>--}}
{{--                            @include('checkout::components.date-field', [--}}
{{--                                'day' => 'travelers.0.passportExpiryDay',--}}
{{--                                'month' => 'travelers.0.passportExpiryMonth',--}}
{{--                                'year' => 'travelers.0.passportExpiryYear'--}}
{{--                            ])--}}
{{--                            @error('travelers.0.passportExpiryDay') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            @error('travelers.0.passportExpiryMonth') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                            @error('travelers.0.passportExpiryYear') <span--}}
{{--                                class="text-red-500 text-sm">{{ $message }}</span> @enderror--}}
{{--                        </div>--}}
{{--                    </div>--}}


                    <div class="flex justify-end mt-8">
                        <button type="button" wire:click="showNextTraveller('{{"$roomNumber-$i" }}')" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                            Next traveller
                        </button>
                    </div>
                </div>
            @endforeach

            @unless($loop->last)
                {{-- Divider between rooms --}}
            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mt-6 mb-6"></div>
            @endunless
        @endforeach


    </form>
</x-checkout::editable-box>
