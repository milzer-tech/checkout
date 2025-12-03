<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Models\Checkout;

class SaveTermAgreementJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $checkoutId,
        public string $key,
        public bool $accepted
    ) {}

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5($this->checkoutId.'-'.$this->key);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = Checkout::query()->where('checkout_id', $this->checkoutId)->firstOrFail();

        $model->updateData([$this->key => $this->accepted]);
    }
}
