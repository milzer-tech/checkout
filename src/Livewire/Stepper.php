<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Stepper extends Component
{
    public $currentPath;

    public function mount()
    {
        $this->currentPath = Route::current()->getName();
    }

    public function navigate($path)
    {
        $this->currentPath = $path;
        $this->dispatch('navigate', path: $path);
    }

    public function isActive($path)
    {
        return $this->currentPath === $path;
    }

    public function isCompleted($stepIndex)
    {
        $currentStep = $this->getCurrentStepIndex();

        return $stepIndex < $currentStep;
    }

    public function getCurrentStepIndex()
    {
        if ($this->currentPath === 'trip.details') {
            return 1;
        }
        if ($this->currentPath === 'payment') {
            return 2;
        }
        if ($this->currentPath === 'confirmation') {
            return 3;
        }

        return 1;
    }

    public function render()
    {
        return view('checkout::trip-details-page.stepper');
    }
}
