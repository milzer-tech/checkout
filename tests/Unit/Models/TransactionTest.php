<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

it('builds Price accessor from amount and currency', function (): void {
    $t = new Transaction;
    $t->amount = '123.45';
    $t->currency = 'EUR';

    $price = $t->price;

    expect($price)
        ->toBeInstanceOf(Price::class)
        ->and($price->amount)->toBe(123.45)
        ->and($price->currency)->toBe('EUR');
});

it('returns BelongsTo relation for checkout()', function (): void {
    $t = new Transaction;

    $relation = $t->checkout();

    expect($relation)
        ->toBeInstanceOf(BelongsTo::class)
        ->and($relation->getRelated()::class)
        ->toBe(Checkout::class);
});

it('defines expected casts and respects enum/decimal casting', function (): void {
    $t = new Transaction;

    $t->status = PaymentStatusEnum::Pending;
    $t->gateway = PaymentGatewayEnum::Oppwa;
    $t->amount = '100.2';
    $t->currency = 'USD';

    $t->prepare_data = ['a' => 1];
    $t->result_data = ['b' => 2];
    $t->nezasa_transaction = ['c' => 3];

    expect($t->status)->toBe(PaymentStatusEnum::Pending)
        ->and($t->gateway)->toBe(PaymentGatewayEnum::Oppwa)
        ->and($t->amount)->toBe('100.20')
        ->and($t->currency)->toBe('USD')
        ->and($t->prepare_data)->toBe(['a' => 1])
        ->and($t->result_data)->toBe(['b' => 2])
        ->and($t->nezasa_transaction)->toBe(['c' => 3]);
});
