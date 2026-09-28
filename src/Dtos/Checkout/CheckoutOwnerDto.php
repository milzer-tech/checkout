<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Dtos\Checkout;

use Carbon\CarbonImmutable;
use Nezasa\Checkout\Dtos\BaseDto;

/**
 * The owner of the checkout customer data, stored in the checkout data under "owner".
 * Only the hash of the key is stored, the key itself stays in the browser cookie.
 */
final class CheckoutOwnerDto extends BaseDto
{
    /**
     * Create a new instance of CheckoutOwnerDto.
     */
    public function __construct(
        public string $token,
        public CarbonImmutable $expiresAt,
    ) {}

    /**
     * Create the owner for the given key, valid for the given minutes.
     */
    public static function issue(string $key, int $minutes): self
    {
        return new self(token: self::hash($key), expiresAt: CarbonImmutable::now()->addMinutes($minutes));
    }

    /**
     * Get a copy that is valid for the given minutes from now.
     */
    public function extend(int $minutes): self
    {
        return new self(token: $this->token, expiresAt: CarbonImmutable::now()->addMinutes($minutes));
    }

    /**
     * Determine if the given key belongs to this owner and has not expired.
     */
    public function isHeldBy(string $key): bool
    {
        return $this->expiresAt->isFuture() && hash_equals($this->token, self::hash($key));
    }

    /**
     * Hash the key the way it is stored.
     */
    private static function hash(string $key): string
    {
        return hash('sha256', $key);
    }
}
