<?php

use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\TripDetailsPage;

Route::get('', TripDetailsPage::class)->name('traveler-details');
