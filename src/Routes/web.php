<?php

declare(strict_types=1);

use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\PaymentPage;
use Nezasa\Checkout\Livewire\PaymentResultPage;
use Nezasa\Checkout\Livewire\TripDetailsPage;
use Nezasa\Checkout\Payments\Gateways\Adyen\ApplePayDomainAssociationController;

Route::get('checkout/details', TripDetailsPage::class)
    ->middleware('web')
    ->name('traveler-details');

Route::get('checkout/payment', PaymentPage::class)
    ->middleware(['web', ValidateSignature::class])
    ->name('payment');

Route::get('checkout/result/{transaction}', PaymentResultPage::class)
    ->middleware('web')
    ->name('payment-result');

// Without the web middleware, as Apple only reads the file and it must not redirect.
Route::get('.well-known/apple-developer-merchantid-domain-association', ApplePayDomainAssociationController::class)
    ->name('apple-pay-domain-association');
