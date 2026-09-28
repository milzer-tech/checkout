<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Nezasa\Checkout\Dtos\Checkout\CheckoutOwnerDto;
use Nezasa\Checkout\Exceptions\CheckoutInUseException;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

/**
 * Binds a checkout to the browser that uses it.
 *
 * The browser holds a random key in a cookie and the checkout data stores its hash under "owner".
 * While the owner is active or may still be paying, every other browser is kept out. Afterwards,
 * the next browser becomes the owner and starts with empty customer data. Remove this class and
 * its calls once checkout users are authenticated, or enable "checkout.customer_data.restore_always".
 */
final class CheckoutOwnership
{
    /**
     * The checkout data keys that hold the customer data.
     */
    public const array CUSTOMER_DATA_KEYS = ['contact', 'paxInfo', 'activityAnswers'];

    /**
     * The checkout data key that holds the owner.
     */
    public const string OWNER_KEY = 'owner';

    /**
     * The keys handed out during the current request, as their cookies only arrive with the next one.
     *
     * @var array<string, string>
     */
    private array $issuedKeys = [];

    /**
     * Determine if the customer data of the checkout is bound to a browser.
     * The rest payment does not show the customer data, so it is not bound.
     */
    public function isProtected(Checkout $checkout): bool
    {
        return ! $checkout->rest_payment && ! Config::boolean('checkout.customer_data.restore_always');
    }

    /**
     * Determine if the current browser may see and change the customer data of the checkout.
     */
    public function isOwner(Checkout $checkout): bool
    {
        return ! $this->isProtected($checkout) || $this->holdsValidKey($checkout);
    }

    /**
     * Determine if the current browser must be kept out, as another browser is still using the checkout.
     * It is in use while its owner is active or may still be paying. The owner itself is never kept out.
     */
    public function isInUseElsewhere(Checkout $checkout): bool
    {
        if (! $this->isProtected($checkout) || $this->holdsValidKey($checkout)) {
            return false;
        }

        return $checkout->getOwner()?->expiresAt->isFuture() === true || $this->hasPendingPayment($checkout);
    }

    /**
     * Make the current browser the owner of the checkout, or extend its key if it already is.
     * A new owner starts with empty customer data, so the data of the previous owner never reaches it.
     *
     * @throws CheckoutInUseException
     */
    public function claim(Checkout $checkout): void
    {
        if (! $this->isProtected($checkout)) {
            return;
        }

        throw_if($this->isInUseElsewhere($checkout), CheckoutInUseException::class);

        if ($this->holdsValidKey($checkout)) {
            $this->extend($checkout);

            return;
        }

        $key = Str::random(64);

        $checkout->updateData([
            ...array_fill_keys(self::CUSTOMER_DATA_KEYS, []),
            self::OWNER_KEY => CheckoutOwnerDto::issue($key, $this->ttl())->toArray(),
        ]);

        $this->issuedKeys[$checkout->id] = $key;
        $this->queueCookie($checkout, $key);
    }

    /**
     * Extend the key of the owner, at most once a minute to avoid a write on every change.
     */
    private function extend(Checkout $checkout): void
    {
        $owner = $checkout->getOwner();

        if (! $owner instanceof CheckoutOwnerDto) {
            return;
        }

        $extended = $owner->extend($this->ttl());

        if ($owner->expiresAt->diffInSeconds($extended->expiresAt) < 60) {
            return;
        }

        $checkout->updateData([self::OWNER_KEY => $extended->toArray()]);

        $this->queueCookie($checkout, $this->currentKey($checkout));
    }

    /**
     * Determine if a payment started within the payment time is still pending.
     * It keeps other browsers out even if the key of the owner expires while paying.
     */
    private function hasPendingPayment(Checkout $checkout): bool
    {
        return $checkout->transactions()
            ->whereStatus(TransactionStatusEnum::Pending)
            ->where('created_at', '>=', now()->subMinutes(Config::integer('checkout.payment_ttl')))
            ->exists();
    }

    /**
     * Get the name of the cookie that holds the key of the checkout.
     */
    public function cookieName(Checkout $checkout): string
    {
        return 'checkout_owner_'.$checkout->id;
    }

    /**
     * Determine if the current browser holds the unexpired key of the checkout.
     */
    private function holdsValidKey(Checkout $checkout): bool
    {
        $key = $this->currentKey($checkout);

        return is_string($key) && $checkout->getOwner()?->isHeldBy($key) === true;
    }

    /**
     * Get the key the current browser holds for the checkout.
     */
    private function currentKey(Checkout $checkout): mixed
    {
        return $this->issuedKeys[$checkout->id] ?? request()->cookie($this->cookieName($checkout));
    }

    /**
     * Send the key to the browser. It is only readable by the server and sent on top-level
     * navigations back from the payment providers.
     */
    private function queueCookie(Checkout $checkout, string $key): void
    {
        Cookie::queue(Cookie::make(
            name: $this->cookieName($checkout),
            value: $key,
            minutes: $this->ttl(),
            secure: request()->isSecure() ? true : null,
            httpOnly: true,
            sameSite: 'lax',
        ));
    }

    /**
     * Get the minutes the key stays valid without any change.
     */
    private function ttl(): int
    {
        return Config::integer('checkout.customer_data.ttl');
    }
}
