<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\AsFluent;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => AsFluent::class,
        ];
    }
}
