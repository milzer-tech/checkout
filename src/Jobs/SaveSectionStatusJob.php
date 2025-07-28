<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Models\Checkout;

class SaveSectionStatusJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $checkoutId,
        public Section $section,
        public bool $isCompleted,
        public bool $isExpanded,
    ) {}

    /**
     * Handle the job to save the section status.
     */
    public function handle(): void
    {
        $model = Checkout::whereCheckoutId($this->checkoutId)->first();

        $model->updateData([
            'status.'.$this->section->value.'.isCompleted' => $this->isCompleted,
            'status.'.$this->section->value.'.isExpanded' => $this->isExpanded,
        ]);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-'.$this->section;
    }
}
