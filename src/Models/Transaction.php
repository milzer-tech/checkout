<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

/**
 * Eloquent model for payment transactions related to a checkout.
 *
 * Scalar/database attributes
 *
 * @property-read int|string $id
 * @property-read int|string|null $checkout_id
 * @property-read array<string, mixed>|null $prepare_data
 * @property-read array<string, mixed>|null $result_data
 * @property-read array<string, mixed>|null $nezasa_transaction
 * @property-read string|null $nezasa_transaction_ref_id
 * @property-read TransactionStatusEnum|null $status
 * @property-read string|null $gateway
 * @property-read string|null $currency
 * @property-read string|null $amount
 *
 * Accessors
 * @property-read Price $price
 *
 * Relationships
 * @property-read Checkout $checkout
 *
 * Timestamps
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class Transaction extends Model
{
    use HasUlids;

    /**
     * {@inheritdoc}
     */
    protected $table = 'checkout_transactions';

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
            'prepare_data' => 'encrypted:json',
            'result_data' => 'encrypted:json',
            'nezasa_transaction' => 'encrypted:json',
            'nezasa_transaction_ref_id' => 'string',
            'status' => TransactionStatusEnum::class,
            'amount' => 'decimal:2',
        ];
    }

    /**
     * The checkout that this transaction belongs to.
     *
     * @return BelongsTo<Checkout, $this>
     */
    public function checkout(): BelongsTo
    {
        return $this->belongsTo(Checkout::class);
    }

    /**
     * Get the price attribute as a Price object.
     *
     * @return Attribute<Price, null>
     */
    protected function price(): Attribute
    {
        return Attribute::get(
            get: fn ($value, array $attributes): Price => new Price((float) $attributes['amount'], $attributes['currency'])
        );
    }
}
