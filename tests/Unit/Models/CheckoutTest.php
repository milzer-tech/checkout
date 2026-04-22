<?php

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Mockery as m;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
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

it('removes deprecated insurance_meta keys from data when insurance is updated', function (): void {
    /** @var Checkout|m\MockInterface $model */
    $model = m::mock(Checkout::class)->makePartial();

    $model->data = new Collection([
        'insurance_meta' => ['x' => 1],
        'insurance_create_offer' => ['y' => 2],
        'insurance_payment' => ['iban' => 'DE'],
        'other' => 'keep',
    ]);

    $model->shouldReceive('save')->once()->andReturnTrue();

    $bucket = InsuranceCheckoutData::emptyInsuranceBucket();
    $ok = $model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate($bucket));

    expect($ok)->toBeTrue();

    $array = $model->data instanceof Collection ? $model->data->toArray() : (array) $model->data;

    expect($array)->not->toHaveKey('insurance_meta')
        ->not->toHaveKey('insurance_create_offer')
        ->not->toHaveKey('insurance_payment')
        ->and($array['other'])->toBe('keep')
        ->and($array['insurance'])->toBe($bucket);
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
