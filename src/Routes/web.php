<?php

use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Livewire\PaymentResult;
use Nezasa\Checkout\Livewire\TripDetailsPage;

Route::get('', TripDetailsPage::class)->name('traveler-details');
Route::get('payment', PaymentPage::class)->name('payment');
Route::get('payment-result', PaymentResult::class)->name('payment-result');
