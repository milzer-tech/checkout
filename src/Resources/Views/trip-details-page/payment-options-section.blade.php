@php
    $state = 'disabled';
@endphp

<x-checkout::editable-box
    title="Payment options"
    :state="$state"
    :showEdit="false"
    :showCheck="false"
>
    <div class="space-y-6 opacity-50 pointer-events-none">
        <div class="flex gap-4">
            <button class="flex-1 p-4 border rounded-lg border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span>Credit Card</span>
                </div>
            </button>
            <button class="flex-1 p-4 border rounded-lg border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.067 8.478c.492.315.844.825.844 1.522 0 1.845-1.534 3.478-3.956 3.478h-2.522c-.315 0-.63.21-.63.525v1.575c0 .315-.315.525-.63.525h-1.575c-.315 0-.63-.21-.63-.525v-1.575c0-.315-.315-.525-.63-.525h-2.522c-2.422 0-3.956-1.633-3.956-3.478 0-.697.352-1.207.844-1.522.315-.21.63-.21.945 0 .315.21.63.525.63.945 0 .525.42.945.945.945h10.5c.525 0 .945-.42.945-.945 0-.42.315-.735.63-.945.315-.21.63-.21.945 0z"/>
                    </svg>
                    <span>PayPal</span>
                </div>
            </button>
        </div>
        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-gray-600 dark:text-gray-300">You will be redirected to PayPal to complete your payment.</p>
        </div>
    </div>
</x-checkout::editable-box>
