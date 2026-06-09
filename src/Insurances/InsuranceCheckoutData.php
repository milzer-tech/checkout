<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/**
 * Checkout {@code data['insurance']} is a single bucket:
 * {@code offer}, {@code meta}, {@code create_offer}, {@code payment}.
 *
 * Legacy layouts are still read: flat offer on {@code insurance}, or split {@code insurance_meta},
 * {@code insurance_create_offer}, {@code insurance_payment}. New writes use only the {@code insurance}
 * bucket; deprecated top-level keys are removed from persisted data (not stored as null).
 *
 * The whole {@code payment} map is encrypted at rest as one JSON blob (Laravel {@code Crypt::encryptString})
 * whenever data is written via {@see prepareInsuranceUpdate}; {@see getPayment} returns the decrypted map for
 * application use. Legacy rows (plaintext payment, or IBAN-only field encryption) are still read until the next save.
 */
final class InsuranceCheckoutData
{
    public const string OFFER = 'offer';

    public const string META = 'meta';

    public const string CREATE_OFFER = 'create_offer';

    public const string PAYMENT = 'payment';

    public const string DECLINED = 'declined';

    public const string PROVIDER = 'provider';

    /**
     * Persisted-only key wrapping the encrypted JSON of the in-memory payment map.
     * Do not use this key in application-level payment DTOs.
     */
    private const string STORED_PAYMENT_ENVELOPE = '__nezasa_insurance_payment_v1';

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
            ->only(['id', 'title', 'price', 'coverage', 'providerMeta', 'terms', 'documentLinks', 'infoLinks'])
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
            return self::decryptPaymentPayloadForUse($ins[self::PAYMENT]);
        }
        $legacy = $checkoutData['insurance_payment'] ?? null;

        return is_array($legacy) ? self::decryptPaymentPayloadForUse($legacy) : [];
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
     * @param  array<string, mixed>  $checkoutData
     */
    public static function isDeclined(array $checkoutData): bool
    {
        $ins = $checkoutData['insurance'] ?? null;

        return is_array($ins) && ($ins[self::DECLINED] ?? false) === true;
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
            self::DECLINED => false,
            self::PROVIDER => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function declinedInsuranceBucket(?string $provider = null): array
    {
        return [
            self::OFFER => null,
            self::META => null,
            self::CREATE_OFFER => null,
            self::PAYMENT => null,
            self::DECLINED => true,
            self::PROVIDER => $provider,
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
        $declined = self::isDeclined($checkoutData);

        if ($offer === null && $meta === null && $createOffer === null && $payment === [] && ! $declined) {
            return null;
        }

        return [
            self::OFFER => $offer,
            self::META => $meta,
            self::CREATE_OFFER => $createOffer,
            self::PAYMENT => $payment === [] ? null : $payment,
            self::DECLINED => $declined,
            self::PROVIDER => is_array($checkoutData['insurance'] ?? null)
                ? ($checkoutData['insurance'][self::PROVIDER] ?? null)
                : null,
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
        if ($insuranceBucket !== null) {
            $insuranceBucket = self::withEncryptedPaymentInBucket($insuranceBucket);
        }

        return [
            'insurance' => $insuranceBucket,
        ];
    }

    /**
     * @param  array<string, mixed>  $bucket
     * @return array<string, mixed>
     */
    private static function withEncryptedPaymentInBucket(array $bucket): array
    {
        $payment = $bucket[self::PAYMENT] ?? null;
        if (! is_array($payment)) {
            return $bucket;
        }

        if ($payment === []) {
            return [...$bucket, self::PAYMENT => null];
        }

        if (self::isPersistedPaymentEnvelope($payment)) {
            try {
                $json = Crypt::decryptString($payment[self::STORED_PAYMENT_ENVELOPE]);
                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return $bucket;
                }
            } catch (DecryptException|JsonException) {
                // Corrupt blob: do not re-wrap (would make recovery harder).
            }

            return $bucket;
        }

        try {
            $json = json_encode($payment, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $bucket;
        }

        return [
            ...$bucket,
            self::PAYMENT => [
                self::STORED_PAYMENT_ENVELOPE => Crypt::encryptString($json),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payment
     * @return array<string, mixed>
     */
    private static function decryptPaymentPayloadForUse(array $payment): array
    {
        if (self::isPersistedPaymentEnvelope($payment)) {
            $wrapped = $payment[self::STORED_PAYMENT_ENVELOPE];
            if (! is_string($wrapped) || $wrapped === '') {
                return [];
            }

            try {
                $decoded = json_decode(Crypt::decryptString($wrapped), true, 512, JSON_THROW_ON_ERROR);

                return is_array($decoded) ? $decoded : [];
            } catch (DecryptException|JsonException) {
                return [];
            }
        }

        if (isset($payment['iban']) && is_string($payment['iban']) && $payment['iban'] !== '') {
            try {
                return [
                    ...$payment,
                    'iban' => Crypt::decryptString($payment['iban']),
                ];
            } catch (DecryptException) {
                return $payment;
            }
        }

        return $payment;
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private static function isPersistedPaymentEnvelope(array $payment): bool
    {
        return count($payment) === 1
            && array_key_exists(self::STORED_PAYMENT_ENVELOPE, $payment)
            && is_string($payment[self::STORED_PAYMENT_ENVELOPE]);
    }
}
