<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Stepper extends Component
{
    /**
     * The current path name.
     */
    public string $currentPath;

    /**
     * Initialize the component and set the current path.
     */
    public function mount(): void
    {
        $this->currentPath = Route::current()->getName();
    }

    /**
     * Check if a given path is the active/current path.
     */
    public function isActive(string $path): bool
    {
        return $this->currentPath === $path;
    }

    /**
     * Check if a step is completed based on its index.
     */
    public function isCompleted(string $stepIndex): bool
    {
        $currentStep = $this->getCurrentStepIndex();

        return $stepIndex <= $currentStep;
    }

    /**
     * Get the index of the current step based on the current path.
     */
    public function getCurrentStepIndex(): int
    {
        if ($this->currentPath === 'traveler-details') {
            return 1;
        }
        if ($this->currentPath === 'payment') {
            return 2;
        }
        if ($this->currentPath === 'payment-result') {
            return 3;
        }

        return 1;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {   /** @phpstan-ignore-next-line */
        return view('checkout::blades.stepper');
    }
}
