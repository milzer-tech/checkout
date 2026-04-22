<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoPlanDetailDto;

it('maps a single DescriptionURL object from the SOAP decoder to a collection', function (): void {
    $dto = ErgoPlanDetailDto::from([
        'Title' => 'Plan',
        'DescriptionURL' => [
            'DefaultInd' => false,
            'Type' => 'INF',
            '_' => 'https://example.com/one',
        ],
    ]);

    expect($dto->DescriptionURL)->toHaveCount(1)
        ->and($dto->DescriptionURL->first()->href())->toBe('https://example.com/one');
});

it('maps multiple DescriptionURL elements', function (): void {
    $dto = ErgoPlanDetailDto::from([
        'Title' => 'Plan',
        'DescriptionURL' => [
            [
                'DefaultInd' => false,
                'Type' => 'TAC',
                '_' => 'https://example.com/tac',
            ],
            [
                'DefaultInd' => true,
                'Type' => 'INF',
                '_' => 'https://example.com/inf',
            ],
        ],
    ]);

    expect($dto->DescriptionURL)->toHaveCount(2)
        ->and($dto->DescriptionURL->last()->href())->toBe('https://example.com/inf');
});
