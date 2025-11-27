<?php

use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Livewire\TripDetailsPage;

Route::get('checkout/details', TripDetailsPage::class)
    ->middleware('web')
    ->name('traveler-details');

Route::get('checkout/payment', PaymentPage::class)
    ->middleware(['web', ValidateSignature::class])
    ->name('payment');

Route::get('checkout/result/{transaction}', PaymentResultPage::class)
    ->middleware('web')
    ->name('payment-result');
