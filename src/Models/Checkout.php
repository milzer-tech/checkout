<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Nezasa\Checkout\Enums\Section;

/**
 * Eloquent model for checkout state.
 *
 * @property string $id
 * @property string $checkout_id
 * @property string $itinerary_id
 * @property string $origin
 * @property string|null $lang
 * @property bool $rest_payment
 * @property Collection<string, mixed>|array<string, mixed>|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * Relationships
 * @property-read EloquentCollection<int, Transaction> $transactions
 * @property-read Transaction|null $lastestTransaction
 *
 * Timestamps
 */
class Checkout extends Model
{
    use HasUlids;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => AsCollection::class,
            'rest_payment' => 'bool',
        ];
    }

    /**
     * Update a specific key in the data collection;
     *
     * @param  array<string, mixed>  $data
     */
    public function updateData(array $data): bool
    {
        /** @phpstan-ignore-next-line */
        $array = $this->data?->toArray() ?? [];

        foreach ($data as $key => $value) {
            $array = data_set($array, $key, $value);
        }

        $this->data = $array;

        return $this->save();
    }

    /**
     * Get the transactions for the checkout.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the latest transaction for the checkout.
     *
     * @return HasOne<Transaction, $this>
     */
    public function lastestTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    /**
     * Check if the checkout is completed for a specific section.
     */
    public function isCompleted(Section $section): bool
    {
        return $this->data['status'][$section->value]['isCompleted'];
    }

    /**
     * Check if the checkout is expanded for a specific section.
     */
    public function isExpanded(Section $section): bool
    {
        return $this->data['status'][$section->value]['isExpanded'];
    }

    public function getAnswer(string $componentId, string $questionRefId): mixed
    {
        return data_get(
            target: $this->data,
            key: "activityAnswers.$componentId.$questionRefId"
        );
    }
}
