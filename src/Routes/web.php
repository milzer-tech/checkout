<?php

use Illuminate\Support\Facades\Route;
use Nezasa\Checkout\Livewire\TravelerDetails;

Route::get('traveler-details', TravelerDetails::class)->name('traveler-details');
