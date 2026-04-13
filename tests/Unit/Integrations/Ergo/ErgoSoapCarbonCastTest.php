<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoSearchTravelerTypeDto;

it('parses Birthdate when SOAP returns simple content as an associative array', function (): void {
    $dto = ErgoSearchTravelerTypeDto::from([
        'ID' => 1,
        'Birthdate' => ['_' => '1999-03-01'],
        'Age' => null,
        'IndCoverageReqs' => null,
        'Extensions' => null,
    ]);

    expect($dto->Birthdate->format('Y-m-d'))->toBe('1999-03-01');
});
