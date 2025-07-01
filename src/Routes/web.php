<?php

use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\TripDetailsPage;

Route::get('traveler-details', TripDetailsPage::class)->name('traveler-details');
