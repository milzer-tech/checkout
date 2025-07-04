<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=mulish:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite([
    'resources/vendor/checkout/css/app.css',
     'resources/vendor/checkout/js/app.js'
     ])
    @livewireStyles
</head>
<body class="h-full">
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 dark:text-white font-mulish">
    <div class="max-w-[1200px] mx-auto px-[30px] py-8">
        <div class="flex justify-end mb-4">
            @include('checkout::components.theme-switcher')
        </div>
        <livewire:stepper />
        <div x-data="{ currentPath: '{{ Route::current()->getName() }}' }"
             x-on:navigate.window="currentPath = $event.detail.path; window.location.href = $event.detail.path === 'trip.details' ? '/' : '/' + $event.detail.path">
            {{ $slot }}
        </div>
    </div>
</div>

@livewireScripts

<script>
    // On page load, set theme from localStorage or system preference
    (function() {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();

    // Global toggle function for theme switcher
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }
</script>
</body>
</html>
