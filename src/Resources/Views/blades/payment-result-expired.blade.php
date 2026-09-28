<div class="flex flex-col min-h-screen">
    <h1 class="text-2xl font-bold mb-6">{{ trans('checkout::page.booking_confirmation.booking_confirmation') }}</h1>

    <div class="max-w-full lg:max-w-[66.66%] space-y-6">
        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">
                {{ trans('checkout::page.booking_confirmation.result_expired_title') }}
            </h2>

            <p class="mt-4 text-sm leading-6 text-gray-700 dark:text-gray-300">
                {{ trans('checkout::page.booking_confirmation.result_expired_message') }}
            </p>

            <x-checkout::contact-support-link
                class="mt-5 inline-flex items-center gap-2 text-[#2681FF] font-medium text-sm hover:underline cursor-pointer"
            >
                {{ trans('checkout::page.trip_details.contact_support') }}
            </x-checkout::contact-support-link>
        </section>

        <div class="flex justify-end">
            <a href="{{ $this->getUrlToTripBuilder }}">
                <button class="px-6 py-3 rounded-md bg-blue-500 text-white hover:bg-blue-600">
                    {{ trans('checkout::page.booking_confirmation.go_back_to_planner') }}
                </button>
            </a>
        </div>
    </div>

    @include('checkout::layouts.footer')
</div>
