<div x-data="{ dark: localStorage.getItem('theme') === 'dark' }" class="flex items-center space-x-2">
    <x-checkout::icons.sun />
    <button
        @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
        :aria-checked="dark"
        role="switch"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-gray-200 dark:bg-blue-600"
    >
        <span :class="dark ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 transform rounded-full bg-white transition"></span>
    </button>
    <x-checkout::icons.moon />
</div>
