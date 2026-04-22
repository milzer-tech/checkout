<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Ergo\Dtos\Responses\ErgoInsurancePlanSearchRSDto;

it('hydrates Travelers when a single Traveler is returned as an associative array', function (): void {
    $dto = ErgoInsurancePlanSearchRSDto::from([
        'MsgId' => 'm',
        'EchoToken' => 'e',
        'TransactionContext' => 't',
        'TimeStamp' => '2026-04-10T10:30:25+02:00',
        'Target' => 'T',
        'Success' => [],
        'Requestor' => null,
        'Travelers' => [
            'Traveler' => [
                'ID' => 1,
                'Birthdate' => '1999-03-01',
                'Age' => null,
                'IndCoverageReqs' => null,
                'Extensions' => null,
            ],
        ],
        'AvailablePlans' => null,
        'Extensions' => null,
        'Errors' => null,
    ]);

    expect($dto->Travelers)->not->toBeNull()
        ->and($dto->Travelers)->toHaveCount(1)
        ->and($dto->Travelers->first()->ID)->toBe(1);
});
