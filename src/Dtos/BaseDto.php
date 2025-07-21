<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

abstract class BaseDto extends Data implements Wireable
{
    use WireableData;

    /**
     * Create a new instance of the DTO from the given payloads.
     */
    public static function from(...$payloads): static
    {
        foreach (static::getManipulatedAttributes() as $attribute) {
            $payloads = self::manipulateDate($payloads, $attribute);
        }

        return parent::from(...$payloads);
    }

    /**
     * Manipulate date fields in the payloads.
     */
    protected static function manipulateDate(array $payloads, string $dateName): array
    {
        $date = $payloads[0][$dateName] ?? null;

        if (
            isset($date['year'], $date['month'], $date['day'])
            && filled($date['year']) && filled($date['month']) && filled($date['day'])
        ) {
            $date = $date['year'].'-'.$date['month'].'-'.$date['day'];

            $payloads[0][$dateName] = $date;
        } else {
            $payloads[0][$dateName] = null;
        }

        //
        //        if($payloads[0]['refId'] === 'pax-3'){
        //            dd($date,
        //                isset($date['year'], $date['month'], $date['day']),
        //                filled($date['year']) && filled($date['month']) && filled($date['day']),
        //                $payloads[0][$dateName]
        //
        //            );
        //
        //        }

        return $payloads;
    }

    /**
     * Get the attributes that need to be manipulated before creating the DTO.
     *
     * @return array<int, string>
     */
    protected static function getManipulatedAttributes(): array
    {
        return [
            // 'dateOfBirth',
        ];
    }
}
