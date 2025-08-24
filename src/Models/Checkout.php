<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Checkout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     */
    public function updateData(array $data): bool
    {
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
     * @return HasMany<Transaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the latest transaction for the checkout.
     *
     * @return HasOne<Transaction>
     */
    public function lastestTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }
}
