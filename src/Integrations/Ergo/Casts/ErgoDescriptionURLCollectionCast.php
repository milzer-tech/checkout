<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Casts;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoDescriptionURLDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoPlanDetailDto;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * SOAP/JSON decoders often return one DescriptionURL as a single associative array; naive
 * collection mapping would iterate scalar values (DefaultInd, Type, …) instead of one item.
 */
final class ErgoDescriptionURLCollectionCast implements Cast
{
    /**
     * @param  CreationContext<ErgoPlanDetailDto>  $context
     * @return Collection<int, ErgoDescriptionURLDto>
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if ($value === null || $value === []) {
            return collect();
        }

        if (! is_array($value)) {
            return collect();
        }

        if (! array_is_list($value) && $this->looksLikeOneDescriptionUrl($value)) {
            $value = [$value];
        }

        /** @var array<int, mixed> $value */
        return collect($value)->map(
            fn (mixed $row): ErgoDescriptionURLDto => ErgoDescriptionURLDto::from($row)
        );
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function looksLikeOneDescriptionUrl(array $value): bool
    {
        return array_key_exists('Type', $value)
            || array_key_exists('_', $value)
            || array_key_exists('DefaultInd', $value);
    }
}
