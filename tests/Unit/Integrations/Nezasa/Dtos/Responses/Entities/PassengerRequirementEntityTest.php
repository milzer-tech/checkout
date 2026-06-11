<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Insurances\Providers\Ergo\ErgoInsurance;
use Nezasa\Checkout\Insurances\Providers\HanseMerkur\HanseMerkurInsurance;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PassengerRequirementEntity;
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

function hiddenPassengerRequirementEntity(): PassengerRequirementEntity
{
    return new PassengerRequirementEntity(
        firstName: TravelerRequirementFieldEnum::Hidden,
        lastName: TravelerRequirementFieldEnum::Hidden,
        secondOrAdditionalName: TravelerRequirementFieldEnum::Hidden,
        gender: TravelerRequirementFieldEnum::Hidden,
        passportNr: TravelerRequirementFieldEnum::Hidden,
        nationality: TravelerRequirementFieldEnum::Hidden,
        birthDate: TravelerRequirementFieldEnum::Hidden,
        passportExpirationDate: TravelerRequirementFieldEnum::Hidden,
        passportIssuingCountry: TravelerRequirementFieldEnum::Hidden,
        street1: TravelerRequirementFieldEnum::Hidden,
        street2: TravelerRequirementFieldEnum::Hidden,
        postalCode: TravelerRequirementFieldEnum::Hidden,
        city: TravelerRequirementFieldEnum::Hidden,
        country: TravelerRequirementFieldEnum::Hidden,
    );
}

it('applies HanseMerkur passenger requirements from the active insurance contract', function (): void {
    Config::set('checkout.insurance.hanse_merkur.active', true);

    $requirements = hiddenPassengerRequirementEntity();

    expect($requirements->firstName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->lastName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->gender)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->birthDate)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->passportNr)->toBe(TravelerRequirementFieldEnum::Hidden);
});

it('applies Ergo passenger requirements from the active insurance contract', function (): void {
    Config::set('checkout.insurance.ergo.active', true);

    $requirements = hiddenPassengerRequirementEntity();

    expect($requirements->firstName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->lastName)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->gender)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->birthDate)->toBe(TravelerRequirementFieldEnum::Required)
        ->and($requirements->passportNr)->toBe(TravelerRequirementFieldEnum::Hidden);
});
