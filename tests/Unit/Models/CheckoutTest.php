<?php

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Mockery as m;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;

it('updates nested data keys and calls save', function (): void {
    /** @var Checkout|m\MockInterface $model */
    $model = m::mock(Checkout::class)->makePartial();

    $model->data = new Collection(['customer' => ['email' => 'old@example.com']]);

    $model->shouldReceive('save')->once()->andReturnTrue();

    $ok = $model->updateData([
        'customer.email' => 'new@example.com',
        'flags.marketing' => true,
    ]);

    expect($ok)->toBeTrue();

    $array = $model->data instanceof Collection ? $model->data->toArray() : (array) $model->data;

    expect(data_get($array, 'customer.email'))->toBe('new@example.com')
        ->and(data_get($array, 'flags.marketing'))->toBeTrue();
});

it('returns HasMany relation for transactions()', function (): void {
    $model = new Checkout;
    $relation = $model->transactions();

    expect($relation)
        ->toBeInstanceOf(HasMany::class)
        ->and($relation->getRelated()::class)
        ->toBe(Transaction::class);
});

it('returns HasOne relation for lastestTransaction()', function (): void {
    $model = new Checkout;
    $relation = $model->lastestTransaction();

    expect($relation)
        ->toBeInstanceOf(HasOne::class)
        ->and($relation->getRelated()::class)
        ->toBe(Transaction::class);
});

it('defines expected casts for data and payment_data', function (): void {
    $model = new Checkout;

    $ref = new ReflectionMethod($model, 'casts');
    /** @var array<string,string> $casts */
    $casts = $ref->invoke($model);

    expect($casts)
        ->toHaveKey('data')
        ->and($casts['data'])->toBe(AsCollection::class)
        ->and($casts)
        ->toHaveKey('payment_data', 'json');
});
