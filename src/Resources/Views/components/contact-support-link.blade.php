@php
    $checkoutSupportEmail = config('checkout.contact_support.email');
@endphp
@if (filled($checkoutSupportEmail))
    <a
        href="mailto:{{ e($checkoutSupportEmail) }}"
        {{ $attributes }}
    >
        {{ $slot }}
    </a>
@else
    <span {{ $attributes }}>
        {{ $slot }}
    </span>
@endif
