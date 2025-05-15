@section('title', 'Page Title')
<div>
    <div class="flex-grow grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section><h1 class="text-2xl font-bold mb-6">Trip details</h1>
                <div class="relative rounded-lg overflow-hidden mb-6"><img
                        src="https://thumbs.dreamstime.com/b/luxury-hotel-bellagio-las-vegas-nv-june-june-usa-casino-located-36820850.jpg"
                        alt="Luxury resort with pool in Palma, Majorca" class="w-full h-48 object-cover">
                    <div class="absolute bottom-0 left-0 p-6 text-white bg-gradient-to-t from-black/70 to-transparent w-full">
                        <h2 class="text-xl font-semibold flex items-center">Lisbon <span
                                class="mx-2">↔</span>Palma, Majorca</h2>
                        <p>Tue, 1 Apr - Sat, 5 Apr</p></div>
                    <button class="absolute bottom-6 right-6 border border-white text-white px-4 py-2 rounded-lg bg-transparent hover:bg-white/10 transition">
                        View full itinerary
                    </button>
                </div>
                <div class="border rounded-lg mb-6 overflow-hidden transition-all duration-300 border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center p-4 cursor-pointer bg-white dark:bg-gray-800">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-check w-5 h-5 text-green-500 mr-2">
                                <path d="M20 6 9 17l-5-5"></path>
                            </svg>
                            <h3 class="font-medium text-lg dark:text-white">Contact details</h3></div>
                        <div class="flex items-center">
                            <button class="

      inline-flex items-center justify-center rounded-xl font-medium
      transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20
      disabled:pointer-events-none disabled:opacity-50


        bg-transparent text-blue-500 dark:text-blue-400
        hover:bg-blue-50 dark:hover:bg-blue-900/20
        hover:text-blue-600 dark:hover:text-blue-300
        px-4 py-2 text-sm

          mr-2
        ">Edit
                            </button>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-chevron-down w-5 h-5 text-gray-400 dark:text-gray-300">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-8 w-full rounded-lg shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.1)] dark:shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.3)]">
                    <div class="flex justify-between items-center mb-8 cursor-pointer">
                        <div><h3 class="font-semibold text-2xl mb-1 dark:text-white">Traveller details</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-base">Information of all the
                                travellers as appearing in their travel documents.</p></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-chevron-up w-5 h-5 text-gray-400 dark:text-gray-300">
                            <path d="m18 15-6-6-6 6"></path>
                        </svg>
                    </div>
                    <form class="space-y-12">
                        <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>
                        <div><h4 class="text-lg font-semibold mb-6">Traveller 1</h4>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-8">
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full"><label
                                        class="block text-gray-700 dark:text-gray-200 font-medium">First
                                        name</label><input type="text" class="form-input"
                                                           placeholder="e.g. Harry"></div>
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full"><label
                                        class="block text-gray-700 dark:text-gray-200 font-medium">Second
                                        name</label><input type="text" placeholder="e.g. James"
                                                           class="form-input"></div>
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full"><label
                                        class="block text-gray-700 dark:text-gray-200 font-medium">Last
                                        name</label><input type="text" placeholder="e.g. Potter"
                                                           class="form-input"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-8 mt-4">
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full col-span-1 md:col-span-3 space-y-2 w-full">
                                    <label class="text-gray-700 dark:text-gray-200 font-medium">Nationality</label>
                                    <div class="relative"><select class="form-input form-input-empty">
                                            <option value="" disabled="">Select</option>
                                            <option value="PT">Portuguese</option>
                                            <option value="ES">Spanish</option>
                                            <option value="US">American</option>
                                            <option value="UK">British</option>
                                        </select><span
                                            class="pointer-events-none absolute inset-y-0 right-4 flex items-center"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-down h-4 w-4 text-gray-400 dark:text-gray-400"><path
                                                    d="m6 9 6 6 6-6"></path></svg></span></div>
                                </div>
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full col-span-1 md:col-span-3 space-y-2 w-full">
                                    <label class="text-gray-700 dark:text-gray-200 font-medium">Gender</label>
                                    <div class="relative"><select class="form-input form-input-empty">
                                            <option value="" disabled="">Select</option>
                                            <option value="M">Male</option>
                                            <option value="F">Female</option>
                                            <option value="O">Other</option>
                                        </select><span
                                            class="pointer-events-none absolute inset-y-0 right-4 flex items-center"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-down h-4 w-4 text-gray-400 dark:text-gray-400"><path
                                                    d="m6 9 6 6 6-6"></path></svg></span></div>
                                </div>
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full"><label
                                        class="block text-gray-700 dark:text-gray-200 font-medium">Date of
                                        birth</label>
                                    <div class="form-input p-0 flex py-1 focus-within:outline-2 focus-within:outline-blue-500 bg-white dark:bg-gray-700 overflow-hidden w-full">
                                        <input type="text" placeholder="DD"
                                               class="w-[20%] min-w-[55px] px-4 outline-none min-w-[50px] text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 dark:outline-none focus:outline-none">
                                        <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
                                        <div class="relative w-[40%]"><select
                                                class="w-full appearance-none outline-none py-2 px-2 pr-8 bg-transparent form-input-empty">
                                                <option value="" disabled="">Month</option>
                                                <option value="01">January</option>
                                                <option value="02">February</option>
                                                <option value="03">March</option>
                                                <option value="04">April</option>
                                                <option value="05">May</option>
                                                <option value="06">June</option>
                                                <option value="07">July</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select><span
                                                class="pointer-events-none absolute inset-y-0 right-2 flex items-center"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-chevron-down h-4 w-4 text-gray-400 dark:text-gray-400"><path
                                                        d="m6 9 6 6 6-6"></path></svg></span></div>
                                        <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
                                        <input type="text" placeholder="YYYY"
                                               class="w-[40%] px-4 outline-none min-w-[50px] text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 dark:outline-none focus:outline-none">
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-8 mt-4">
                                <div class="col-span-1 md:col-span-3 space-y-2 w-full"><label
                                        class="block text-gray-700 dark:text-gray-200 font-medium">Passport
                                        number</label><input type="text" placeholder="e.g. AB123456"
                                                             class="form-input"></div>
                                <div class="col-span-1 md:col-span-4 space-y-2 w-full "><label
                                        class="text-gray-700 dark:text-gray-200 font-medium">Passport
                                        issuing country</label>
                                    <div class="relative"><select class="form-input form-input-empty">
                                            <option value="" disabled="">Select</option>
                                            <option value="PT">Portugal</option>
                                            <option value="ES">Spain</option>
                                            <option value="US">United States</option>
                                            <option value="UK">United Kingdom</option>
                                        </select><span
                                            class="pointer-events-none absolute inset-y-0 right-4 flex items-center"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-down h-4 w-4 text-gray-400 dark:text-gray-400"><path
                                                    d="m6 9 6 6 6-6"></path></svg></span></div>
                                </div>
                                <div class="col-span-1 md:col-span-5 space-y-2 w-full"><label
                                        class="block text-gray-700 dark:text-gray-200 font-medium">Passport
                                        expiration date</label>
                                    <div class="form-input p-0 flex py-1 focus-within:outline-2 focus-within:outline-blue-500 bg-white dark:bg-gray-700 overflow-hidden w-full">
                                        <input type="text" placeholder="DD"
                                               class="w-[20%] min-w-[55px] px-4 outline-none min-w-[50px] text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 dark:outline-none focus:outline-none">
                                        <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
                                        <div class="relative w-[40%]"><select
                                                class="w-full appearance-none outline-none py-2 px-2 pr-8 bg-transparent form-input-empty">
                                                <option value="" disabled="">Month</option>
                                                <option value="01">January</option>
                                                <option value="02">February</option>
                                                <option value="03">March</option>
                                                <option value="04">April</option>
                                                <option value="05">May</option>
                                                <option value="06">June</option>
                                                <option value="07">July</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select><span
                                                class="pointer-events-none absolute inset-y-0 right-2 flex items-center"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-chevron-down h-4 w-4 text-gray-400 dark:text-gray-400"><path
                                                        d="m6 9 6 6 6-6"></path></svg></span></div>
                                        <div class="w-px bg-gray-200 dark:bg-gray-600 my-2"></div>
                                        <input type="text" placeholder="YYYY"
                                               class="w-[40%] px-4 outline-none min-w-[50px] text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 dark:outline-none focus:outline-none">
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="button"
                                        class="text-blue-600 hover:underline flex items-center gap-1 font-medium">
                                    Next traveller
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="lucide lucide-chevron-down w-4 h-4">
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8">
                            <button class="

      inline-flex items-center justify-center rounded-xl font-medium
      transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20
      disabled:pointer-events-none disabled:opacity-50


        bg-blue-500 dark:bg-blue-600 text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-lg font-medium text-sm py-[8px] px-[16px]


        " type="submit">Next
                            </button>
                        </div>
                    </form>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-6 w-full rounded-lg shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.1)] dark:shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.3)] mb-6 mt-6">
                    <div class="flex justify-between items-center cursor-pointer">
                        <div><h3 class="font-semibold text-xl sm:text-2xl dark:text-white">Add promo
                                code</h3></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-chevron-down w-5 h-5 text-gray-400 dark:text-gray-300">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-6 w-full rounded-lg shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.1)] dark:shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.3)] mb-6 mt-6">
                    <div class="flex justify-between items-center cursor-pointer">
                        <div><h3 class="font-semibold text-xl sm:text-2xl dark:text-white">Travel
                                insurance</h3></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-chevron-down w-5 h-5 text-gray-400 dark:text-gray-300">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-6 w-full rounded-lg shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.1)] dark:shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.3)] mb-6 mt-6">
                    <div class="flex justify-between items-center cursor-pointer">
                        <div><h3 class="font-semibold text-xl sm:text-2xl dark:text-white">Additional
                                services</h3></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-chevron-down w-5 h-5 text-gray-400 dark:text-gray-300">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-6 w-full rounded-lg shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.1)] dark:shadow-[0px_0px_20px_0px_rgba(0,_0,_0,_0.3)] mb-6 mt-6">
                    <div class="flex justify-between items-center cursor-pointer">
                        <div><h3 class="font-semibold text-xl sm:text-2xl dark:text-white">Payment
                                options</h3></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-chevron-down w-5 h-5 text-gray-400 dark:text-gray-300">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-6"></div>
            </section>
        </div>

        @include('checkout::traveler-details.right-bar')

    </div>
    <div class="mt-10 mb-6 flex justify-between max-w-full lg:max-w-[66.66%]">
        <button class="

      inline-flex items-center justify-center rounded-xl font-medium
      transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20
      disabled:pointer-events-none disabled:opacity-50


        bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
        border border-gray-300 dark:border-gray-600
        hover:bg-gray-50 dark:hover:bg-gray-700
        px-4 py-2 text-sm

          flex items-center gap-2 px-6 py-3 rounded-md
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="lucide lucide-arrow-left w-4 h-4">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M19 12H5"></path>
            </svg>
            Back
        </button>
        <button class="

      inline-flex items-center justify-center rounded-xl font-medium
      transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20
      disabled:pointer-events-none disabled:opacity-50


        bg-blue-500 dark:bg-blue-600 text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-lg font-medium text-sm py-[8px] px-[16px]

          bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-md
        ">Pay 123 € (EUR)
        </button>
    </div>
</div>


