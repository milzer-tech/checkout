<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Eloquent model for checkout state.
 *
 * @property string $checkout_id
 * @property string|null $itinerary_id
 * @property Collection<string, mixed>|array<string, mixed>|null $data
 * @property array<string, string|int>|null $payment_data
 *
 * Relationships
 * @property-read EloquentCollection<int, Transaction> $transactions
 * @property-read Transaction|null $lastestTransaction
 *
 * Timestamps
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class Checkout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * {@inheritdoc}
     */
    protected $fillable = [
        'checkout_id',
        'itinerary_id',
        'data',
        'payment_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => AsCollection::class,
            'payment_data' => 'json',
        ];
    }

    /**
     * Update a specific key in the data collection;
     *
     * @param  array<string, mixed>  $data
     */
    public function updateData(array $data): bool
    {   /** @phpstan-ignore-next-line  */
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
}
