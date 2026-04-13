<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Casts;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Normalizes SOAP/JSON decoded structures (arrays, optional wrapper keys, single vs repeated elements)
 * into a {@see Collection} of {@see Data} objects.
 */
final readonly class ErgoSoapDataCollectionCast implements Cast
{
    /**
     * @param  array<int, string>  $singleItemAnyOfKeys  Non-empty: wrap a non-list associative row when any key is present
     */
    public function __construct(
        private string $itemClass,
        private ?string $unwrapKey = null,
        private array $singleItemAnyOfKeys = [],
        private bool $nullWhenInputNull = false,
    ) {}

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if ($value === null) {
            return $this->nullWhenInputNull ? null : collect();
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (! is_array($value)) {
            return collect();
        }

        if ($value === []) {
            return collect();
        }

        $rows = $value;

        if ($this->unwrapKey !== null && array_key_exists($this->unwrapKey, $rows)) {
            $rows = $rows[$this->unwrapKey];
            if ($rows === null) {
                return collect();
            }
        }

        if (! is_array($rows)) {
            return collect();
        }

        if ($rows === []) {
            return collect();
        }

        if (! array_is_list($rows) && $this->looksLikeSingleRow($rows)) {
            $rows = [$rows];
        }

        if (! array_is_list($rows)) {
            return collect();
        }

        $class = $this->itemClass;

        return collect($rows)->map(
            function (mixed $row) use ($class): BaseData {
                if ($row instanceof $class) {
                    return $row;
                }
                if ($row instanceof Data) {
                    return $class::from($row->toArray());
                }
                if (is_array($row)) {
                    return $class::from($row);
                }

                throw new \InvalidArgumentException(
                    'Expected array or '.$class.' for SOAP collection item, got '.get_debug_type($row).'.'
                );
            }
        );
    }

    /**
     * @param  array<string, mixed>  $rows
     */
    private function looksLikeSingleRow(array $rows): bool
    {
        if ($this->singleItemAnyOfKeys === []) {
            return false;
        }

        foreach ($this->singleItemAnyOfKeys as $key) {
            if (array_key_exists($key, $rows)) {
                return true;
            }
        }

        return false;
    }
}
