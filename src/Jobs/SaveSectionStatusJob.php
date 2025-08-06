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
        public ?bool $isExpanded = null,
    ) {}

    /**
     * Create a new instance of SaveSectionStatusJob statically.
     */
    public static function make(string $checkoutId, Section $section, bool $isCompleted, ?bool $isExpanded = null): self
    {
        return new self($checkoutId, $section, $isCompleted, $isExpanded);
    }

    /**
     * Handle the job to save the section status.
     */
    public function handle(): void
    {
        $model = Checkout::whereCheckoutId($this->checkoutId)->first();

        $data = [
            'status.'.$this->section->value.'.isCompleted' => $this->isCompleted,
        ];

        if ($this->isExpanded) {
            $data['status.'.$this->section->value.'.isExpanded'] = $this->isExpanded;
        }

        $model->updateData($data);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->checkoutId.'-'.$this->section->name;
    }
}
