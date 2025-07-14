<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'checkout_id',
        'data',
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
        ];
    }

    /**
     * Update a specific key in the data collection;
     */
    public function updateData(string $kay, $value): bool
    {
        $array = $this->data?->toArray() ?? [];

        $array = data_set($array, $kay, $value);

        $this->data = $array;

        return $this->save();
    }
}
