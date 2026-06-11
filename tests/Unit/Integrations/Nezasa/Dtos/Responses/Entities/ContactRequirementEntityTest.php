<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Insurances\Providers\HanseMerkur\HanseMerkurInsurance;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ContactRequirementEntity;
use Nezasa\Checkout\Integrations\Nezasa\Enums\TravelerRequirementFieldEnum;

beforeEach(function (): void {
    Config::set('checkout.insurance_provider', [
        ErgoInsurance::class,
        HanseMerkurInsurance::class,
    ]);
    Config::set('checkout.insurance.vertical.active', false);
    Config::set('checkout.insurance.ergo.active', false);
    Config::set('checkout.insurance.hanse_merkur.active', false);
});

function hiddenContactRequirementEntity(): ContactRequirementEntity
{
    return new ContactRequirementEntity(
        firstName: TravelerRequirementFieldEnum::Hidden,
        lastName: TravelerRequirementFieldEnum::Hidden,
        companyName: TravelerRequirementFieldEnum::Hidden,
        email: TravelerRequirementFieldEnum::Hidden,
        mobilePhone: TravelerRequirementFieldEnum::Hidden,
        street1: TravelerRequirementFieldEnum::Hidden,
        street2: TravelerRequirementFieldEnum::Hidden,
        postalCode: TravelerRequirementFieldEnum::Hidden,
        city: TravelerRequirementFieldEnum::Hidden,
        country: TravelerRequirementFieldEnum::Hidden,
        state: TravelerRequirementFieldEnum::Hidden,
        gender: TravelerRequirementFieldEnum::Hidden,
        taxNumber: TravelerRequirementFieldEnum::Hidden,
        localIdNumber: TravelerRequirementFieldEnum::Hidden,
    );
}

it('applies HanseMerkur contact requirements from the active insurance contract', function (): void {
    Config::set('checkout.insurance.hanse_merkur.active', true);

    $requirements = hiddenContactRequirementEntity();

    expect($requirements->firstName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->lastName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->email)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->street1)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->postalCode)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->country)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->gender)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->city)->toBe(TravelerRequirementFieldEnum::Hidden);
});

it('applies Ergo contact requirements from the active insurance contract', function (): void {
    Config::set('checkout.insurance.ergo.active', true);

    $requirements = hiddenContactRequirementEntity();

    expect($requirements->firstName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->lastName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->email)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->street1)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->postalCode)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->country)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->gender)->toBe(TravelerRequirementFieldEnum::Hidden);
});
