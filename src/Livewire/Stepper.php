<?php

namespace Nezasa\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Stepper extends Component
{
    public string $currentPath;

    public function mount(): void
    {
        $this->currentPath = Route::current()->getName();
    }

    public function navigate(string $path): void
    {
        $this->currentPath = $path;

        $this->dispatch('navigate', path: $path);
    }

    public function isActive(string $path): bool
    {
        return $this->currentPath === $path;
    }

    public function isCompleted(string $stepIndex): bool
    {
        $currentStep = $this->getCurrentStepIndex();

        return $stepIndex <= $currentStep;
    }

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

    public function render(): View
    {   /** @phpstan-ignore-next-line */
        return view('checkout::blades.stepper');
    }
}
