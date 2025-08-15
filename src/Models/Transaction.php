<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class Transaction extends Model
{
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
            'prepare_data' => 'json',
            'result_data' => 'json',
            'nezasa_transaction' => 'json',
            'nezasa_transaction_ref_id' => 'string',
            'status' => PaymentStatusEnum::class,
            'gateway' => PaymentGatewayEnum::class,
            'amount' => 'decimal:2',
        ];
    }

    /**
     * The checkout that this transaction belongs to.
     *
     * @return BelongsTo<Checkout, self>
     */
    public function checkout(): BelongsTo
    {
        return $this->belongsTo(Checkout::class);
    }
}
