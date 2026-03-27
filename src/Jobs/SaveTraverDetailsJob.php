<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Nezasa\Checkout\Models\Checkout;

class SaveTraverDetailsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $checkoutId,
        public string $name,
        public mixed $value
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = Checkout::query()->firstOrCreate(['checkout_id' => $this->checkoutId]);

        $value = $this->value;

        if (preg_match('/\.(birthDate|passportExpirationDate)$/', $this->name) === 1) {
            $existing = data_get($model->data, $this->name);
            if ($existing instanceof \Illuminate\Support\Collection) {
                $existing = $existing->all();
            }
            $value = $this->mergeTripDate(is_array($existing) ? $existing : [], is_array($value) ? $value : []);
        }

        $model->updateData([$this->name => $value]);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, int>
     */
    private function mergeTripDate(array $existing, array $incoming): array
    {
        $out = [];

        foreach (['day', 'month', 'year'] as $key) {
            $new = $incoming[$key] ?? null;
            $old = $existing[$key] ?? null;
            $chosen = ($new !== null && $new !== '') ? $new : $old;

            if ($chosen !== null && $chosen !== '') {
                $out[$key] = (int) $chosen;
            }
        }

        return $out;
    }
}
