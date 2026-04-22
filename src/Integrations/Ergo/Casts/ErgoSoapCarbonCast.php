<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Casts;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * SOAP decoders often return xs:date / xs:dateTime as a string, but sometimes as a one-element structure
 * (e.g. simple content). Passing an array into Spatie's date cast triggers "Array to string conversion".
 */
final readonly class ErgoSoapCarbonCast implements Cast
{
    public function __construct(
        private bool $nullable = false,
    ) {}

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if ($value === null || $value === '') {
            if ($this->nullable) {
                return null;
            }

            throw new \InvalidArgumentException('SOAP date value is empty but property is not nullable.');
        }

        $string = self::normalizeToDateString($value);

        if ($string === null || $string === '') {
            if ($this->nullable) {
                return null;
            }

            throw new \InvalidArgumentException('Could not normalize SOAP date value to a string.');
        }

        return Carbon::parse($string);
    }

    public static function normalizeToDateString(mixed $value): ?string
    {
        if (is_string($value)) {
            return preg_replace('/(\.\d{6})\d+/', '$1', $value) ?? $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s.u');
        }

        if (! is_array($value)) {
            return null;
        }

        foreach (['_', 'value', '#', '__text'] as $key) {
            if (isset($value[$key]) && is_string($value[$key])) {
                return $value[$key];
            }
        }

        foreach ($value as $v) {
            if (is_string($v)) {
                return preg_replace('/(\.\d{6})\d+/', '$1', $v) ?? $v;
            }
        }

        return null;
    }
}
