<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Supporters;

final class AutoCompleteSupporter
{
    /**
     * List of autocomplete tokens for various fields
     *
     * @var array<string, string>
     */
    public static array $tokens = [
        'firstName' => 'given-name',
        'lastName' => 'family-name',
        'secondOrAdditionalName' => 'additional-name',
        'gender' => 'sex',
        'passportNr' => 'passport', //
        'nationality' => 'country',
        'year' => 'bday-year',
        'month' => 'bday-month',
        'day' => 'bday-day',
        'passportIssuingCountry' => 'country',
        'street1' => 'address-line1',
        'street2' => 'address-line2',
        'postalCode' => 'postal-code',
        'city' => 'address-level2',
        'country' => 'country',
        'companyName' => 'organization',
        'email' => 'email',
        'mobilePhone' => 'tel',
        'state' => 'region',
        'taxNumber' => 'tax-id',
        'localIdNumber' => 'tax-id',
    ];

    public static function get(string $field): string
    {
        return isset(self::$tokens[$field])
            ? 'autocomplete="'.self::$tokens[$field].'"'
            : '';
    }
}
