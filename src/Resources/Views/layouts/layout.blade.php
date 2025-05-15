<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="vendor/checkout/css/styles/base.css">
    <link rel="stylesheet" href="vendor/checkout/css/index.css">

    @stack('scripts')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body data-new-gr-c-s-check-loaded="14.1234.0" data-gr-ext-installed="">
<div id="root">
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 dark:text-white font-mulish">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex justify-end mb-4">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="lucide lucide-sun h-[1.2rem] w-[1.2rem] text-yellow-500 dark:text-yellow-300">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2"></path>
                        <path d="M12 20v2"></path>
                        <path d="m4.93 4.93 1.41 1.41"></path>
                        <path d="m17.66 17.66 1.41 1.41"></path>
                        <path d="M2 12h2"></path>
                        <path d="M20 12h2"></path>
                        <path d="m6.34 17.66-1.41 1.41"></path>
                        <path d="m19.07 4.93-1.41 1.41"></path>
                    </svg>
                    <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500/20">
                        <input type="checkbox" class="
            absolute inset-0 h-full w-full cursor-pointer opacity-0

          " aria-label="Toggle dark mode" checked=""><span class="
            pointer-events-none absolute inset-0 rounded-full bg-gray-200
            transition-colors duration-200 ease-in-out
            bg-gray-200 dark:bg-gray-700
          "></span><span class="
            pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full
            bg-white shadow-sm ring-0
            transition-transform duration-200 ease-in-out
            translate-x-0
            dark:bg-gray-200
          "></span></div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="lucide lucide-moon h-[1.2rem] w-[1.2rem] text-gray-400 dark:text-blue-200">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                </div>
            </div>
            @include('checkout::layouts.step')
            <main>
                <div class="flex flex-col min-h-screen">
                    {{$slot}}

                    @include('checkout::layouts.footer')
                </div>
            </main>

        </div>
    </div>
</div>
@livewireScripts
</body>
</html>
