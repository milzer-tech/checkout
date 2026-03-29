<?php

use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Livewire\TripDetailsPage;
use Nezasa\Checkout\Middleware\SetLocale;

Route::get('checkout/details', TripDetailsPage::class)
    ->middleware(['web', SetLocale::class])
    ->name('traveler-details');

Route::get('checkout/payment', PaymentPage::class)
    ->middleware(['web', ValidateSignature::class, SetLocale::class])
    ->name('payment');

Route::get('checkout/result/{transaction}', PaymentResultPage::class)
    ->middleware(['web', SetLocale::class])
    ->name('payment-result');
