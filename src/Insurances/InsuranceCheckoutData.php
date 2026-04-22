<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances;

use Illuminate\Support\Collection;

/**
 * Checkout {@code data['insurance']} is a single bucket:
 * {@code offer}, {@code meta}, {@code create_offer}, {@code payment}.
 *
 * Legacy layouts are still read: flat offer on {@code insurance}, or split {@code insurance_meta},
 * {@code insurance_create_offer}, {@code insurance_payment}. New writes use only the {@code insurance}
 * bucket; deprecated top-level keys are removed from persisted data (not stored as null).
 */
final class InsuranceCheckoutData
{
    public const string OFFER = 'offer';

    public const string META = 'meta';

    public const string CREATE_OFFER = 'create_offer';

    public const string PAYMENT = 'payment';

    /**
     * @param  Collection<string, mixed>|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function checkoutDataArray(Collection|array $data): array
    {
        return $data instanceof Collection ? $data->toArray() : $data;
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, mixed>|null InsuranceOfferDto array shape, or null
     */
    public static function getOffer(array $checkoutData): ?array
    {
        $ins = $checkoutData['insurance'] ?? null;
        if (! is_array($ins)) {
            return null;
        }
        if (array_key_exists(self::OFFER, $ins)) {
            $offer = $ins[self::OFFER];

            return is_array($offer) ? $offer : null;
        }
        if (isset($ins['id'])) {
            return self::legacyFlatInsuranceRootToOfferArray($ins);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $insuranceRoot  Legacy: insurance was the offer DTO array
     * @return array<string, mixed>
     */
    private static function legacyFlatInsuranceRootToOfferArray(array $insuranceRoot): array
    {
        return collect($insuranceRoot)
            ->only(['id', 'title', 'price', 'coverage', 'providerMeta', 'terms'])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     */
    public static function getMeta(array $checkoutData): mixed
    {
        $ins = $checkoutData['insurance'] ?? null;
        if (is_array($ins) && array_key_exists(self::META, $ins)) {
            return $ins[self::META];
        }

        return $checkoutData['insurance_meta'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, mixed>|null
     */
    public static function getCreateOffer(array $checkoutData): ?array
    {
        $ins = $checkoutData['insurance'] ?? null;
        if (is_array($ins) && array_key_exists(self::CREATE_OFFER, $ins)) {
            $c = $ins[self::CREATE_OFFER];

            return is_array($c) ? $c : null;
        }
        $legacy = $checkoutData['insurance_create_offer'] ?? null;

        return is_array($legacy) ? $legacy : null;
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, mixed>
     */
    public static function getPayment(array $checkoutData): array
    {
        $ins = $checkoutData['insurance'] ?? null;
        if (is_array($ins) && array_key_exists(self::PAYMENT, $ins) && is_array($ins[self::PAYMENT])) {
            return $ins[self::PAYMENT];
        }
        $legacy = $checkoutData['insurance_payment'] ?? null;

        return is_array($legacy) ? $legacy : [];
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     */
    public static function hasSelectedOffer(array $checkoutData): bool
    {
        $offer = self::getOffer($checkoutData);

        return is_array($offer) && isset($offer['id']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyInsuranceBucket(): array
    {
        return [
            self::OFFER => null,
            self::META => null,
            self::CREATE_OFFER => null,
            self::PAYMENT => null,
        ];
    }

    /**
     * Normalized bucket for persistence, or null if nothing to store.
     *
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, mixed>|null
     */
    public static function getNormalizedInsuranceBucket(array $checkoutData): ?array
    {
        $offer = self::getOffer($checkoutData);
        $meta = self::getMeta($checkoutData);
        $createOffer = self::getCreateOffer($checkoutData);
        $payment = self::getPayment($checkoutData);

        if ($offer === null && $meta === null && $createOffer === null && $payment === []) {
            return null;
        }

        return [
            self::OFFER => $offer,
            self::META => $meta,
            self::CREATE_OFFER => $createOffer,
            self::PAYMENT => $payment === [] ? null : $payment,
        ];
    }

    /**
     * Remove deprecated top-level keys from checkout JSON (they now live under {@code insurance}).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function stripLegacyInsuranceKeys(array $data): array
    {
        unset($data['insurance_meta'], $data['insurance_create_offer'], $data['insurance_payment']);

        return $data;
    }

    /**
     * @param  array<string, mixed>|null  $insuranceBucket
     * @return array<string, mixed>
     */
    public static function prepareInsuranceUpdate(?array $insuranceBucket): array
    {
        return [
            'insurance' => $insuranceBucket,
        ];
    }
}
