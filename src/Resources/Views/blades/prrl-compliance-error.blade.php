<div class="flex flex-col min-h-screen">
    <h1 class="text-2xl font-bold mb-6">{{ trans('checkout::page.trip_details.trip_details') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr] gap-8">
        <div class="space-y-6">
            <section class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex gap-4">
                        <div class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center text-red-500">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 8.586 14.293 4.293l1.414 1.414L11.414 10l4.293 4.293-1.414 1.414L10 11.414l-4.293 4.293-1.414-1.414L8.586 10 4.293 5.707l1.414-1.414L10 8.586Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                {{ trans('checkout::page.trip_details.prrl_compliance_error_title') }}
                            </h2>
                            <p class="mt-4 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                {{ trans('checkout::page.trip_details.prrl_compliance_error_message') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="min-w-0">
            <livewire:trip-summary
                :$itinerary
                :$model
                :is-completed="$model->isCompleted(\Nezasa\Checkout\Enums\Section::Summary)"
                :is-expanded="$model->isExpanded(\Nezasa\Checkout\Enums\Section::Summary)"
                :traveller-processed="$model->isCompleted(\Nezasa\Checkout\Enums\Section::Traveller)"
            />
        </div>
    </div>

    <div class="mt-10 mb-6 flex justify-end max-w-full md:max-w-[64.66%]">
        <a href="{{ $this->getUrlToTripBuilder }}">
            <button class="px-6 py-3 rounded-md bg-blue-500 text-white hover:bg-blue-600">
                {{ trans('checkout::page.booking_confirmation.go_back_to_planner') }}
            </button>
        </a>
    </div>

    @include('checkout::layouts.footer')
</div>
