<?php

use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Livewire\TripDetailsPage;

Route::get('checkout/details', TripDetailsPage::class)
    ->name('traveler-details');

Route::get('checkout/payment', PaymentPage::class)
    ->middleware(ValidateSignature::class)
    ->name('payment');

Route::get('checkout/result', PaymentResultPage::class)
    ->name('payment-result');
