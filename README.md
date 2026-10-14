# Checkout Process for Nezasa Web Application

This Laravel package provides a **complete checkout process** for the Nezasa web application.  
It integrates with **Nezasa’s APIs** to handle all the necessary steps for booking an itinerary, ensuring a smooth and reliable booking experience.

## Requirements
Make sure your environment meets the following requirements:

1. **PHP** 8.3 or higher
2. **MySQL** 8
3. **Laravel** 11 or higher
4. **Redis ** 

The package relies heavily on cache** and **queues** to ensure better performance.  
It is **highly recommended** to use **Redis** as both the queue driver and cache driver for optimal speed and reliability.

## Installation & Setup
This package is already installed and pre-configured in the [checkout-main-app repository](https://github.com/milzer-tech/checkout-main-app).

The repository also includes useful configurations and dependencies to help you set up and run a Laravel application faster:

- **Vite configuration** for asset bundling and front-end builds
- **Laravel Horizon** for managing and monitoring queues

With these configurations in place, you can quickly get started with the Nezasa checkout.

## Setup on Nezasa instance
After deploying the laravel application, you need to chanege the following settings on your Nezasa instance:
- Go to Nezasa cockpit
- Then to Settings
- Select your instance
- Click on the checkout tab
- Scroll down to custom checkout 
- Change configuration to your laravel application url like 
```
http://your-domain.com/checkout/details?checkoutId=${CHECKOUT_ID}&itineraryId=${ITINERARY_ID}&origin=${ORIGIN}
```

## Features of the Package
Below is a list of the key features included in this package:

1. **Itinerary Summary** – Summarizes itinerary details such as travel dates, travelers, and pricing.
2. **Contact Form Handling** – Customizes the display and validation of contact input fields based on the Nezasa API.
3. **Traveler Form Handling** – Customizes the display and validation of traveler input fields according to the Nezasa API. The page dynamically adapts to the number of rooms and travelers.
4. **Country List Integration** – Displays the list of countries retrieved from the Nezasa API.
5. **Availability Check** – Automatically checks itinerary availability once the required data is provided, working seamlessly in the background.
6. **Promotional Codes** – Allows applying promotional codes to a booking through the Nezasa API.
7. **Additional Services** – Supports adding and removing extra services in a booking using the Nezasa API.
8. **Payment Provider Integration** – Provides a flexible design to integrate different payment providers into the project. (Currently, only **Oppwa** is supported.)
9. **Transaction Handling** – Initiates and stores transaction data via the Nezasa API.
10. **Booking Confirmation** – Manages booking confirmation and displays the booking reference.
11. **Error & Exception Handling** – Handles errors gracefully, with user-friendly messages and fallback options.
12. **Save Booking State** – Preserves the current state of the booking process. If a user leaves the page, cancels the payment or reloads it, the entered data is restored in the same browser for a limited time (see [Customer data ownership](#customer-data-ownership)).
13. **Multi-language Support** – Includes localization for English, German, French, and Spanish, with easy extensibility for additional languages.
14. **Configuration Options** – Offers flexible configuration to adapt the package to project needs, such as updating payment provider credentials.
15. **Best Practices** – Follows industry best practices for security, performance, and code quality, ensuring a robust and maintainable package.

---

### Setting up Configuration

Add the following variables to the `.env` file of your Laravel application:

```bash
# Nezasa API
CHECKOUT_NEZASA_BASE_URL="nezasa trip builder api url"
CHECKOUT_NEZASA_USERNAME="username"
CHECKOUT_NEZASA_PASSWORD="password"
```

### Customer data ownership
A checkout is bound to the browser that uses it, as checkout users are not authenticated and anyone may hold the checkout link.

- The browser that opens the checkout while nobody else uses it becomes its owner and receives a random key in an HTTP-only cookie. Only the hash of the key and its expiry are stored, under `owner` in the checkout data, so no migration is needed.
- While the owner is active, any other browser that opens the checkout or its payment result gets a "This checkout is open in another browser. Please try again later." page, so it can neither see nor change the data.
- The payment result is only shown to the owner. Once the owner is no longer active, other browsers get a "This page has expired" page without any booking details. The payment itself is always processed.
- The key is valid for `CHECKOUT_CUSTOMER_DATA_TTL` minutes and is extended by every activity, so the owner can leave the page or cancel the payment and continue. Once the owner has been inactive for that time, the next browser becomes the owner and starts with empty contact, traveller and activity data.
- When the user cancels at the payment provider and returns, the pending transaction is marked as canceled.
- The rest payment is not bound to a browser, as it does not show these sections.
- `CHECKOUT_PAYMENT_TTL` is the time a payment may take. It is used for:
  - the payment page link and the Stripe Checkout Session (Stripe accepts 30 to 1440 minutes),
  - the rest payment result page, which is only shown for this time after the transaction was created, as the rest payment is not bound to a browser,
  - keeping other browsers out while a payment started within this time is still pending, even if the owner's key has expired.
- Keep `CHECKOUT_CUSTOMER_DATA_TTL` longer than `CHECKOUT_PAYMENT_TTL`, so the owner's key does not expire while paying.

```dotenv
CHECKOUT_CUSTOMER_DATA_TTL=120
CHECKOUT_PAYMENT_TTL=60
```

Once checkout users are authenticated, the data can be shown to everyone who opens the checkout link again:

```dotenv
CHECKOUT_RESTORE_CUSTOMER_DATA=true
```

### Passolution travel information
Passolution travel information is shown only when all required conditions are met:

1. Passolution is enabled in the checkout configuration.
2. A valid Passolution token is configured.
3. The Nezasa regulatory information response has travel information confirmation enabled for the checkout.

Add the following variables to the `.env` file of your Laravel application:

```dotenv
# Passolution Travel Information
CHECKOUT_PASSOLUTION_ACTIVE=true
CHECKOUT_PASSOLUTION_BASE_URL="https://api.passolution.eu/api/v2"
CHECKOUT_PASSOLUTION_TOKEN="your passolution token"
```

The checkout will not show the travel information confirmation step if Nezasa does not return `travelInformation.confirmationEnabled = true`, even when Passolution is active and a token is configured.

### Country priority options
You can pin selected countries to the top of country dropdowns. The order of `CHECKOUT_PRIORITIZED_COUNTRY_CODES` controls the order shown to the user.

```dotenv
# Country Dropdown Priorities
CHECKOUT_PRIORITIZED_COUNTRY_CODES="DE,AT,CH"
CHECKOUT_PRIORITIZED_COUNTRY_FIELDS="nationality,country"
```

`CHECKOUT_PRIORITIZED_COUNTRY_CODES` accepts comma-separated ISO 3166-1 alpha-2 country codes. Missing or duplicate country codes are ignored.

`CHECKOUT_PRIORITIZED_COUNTRY_FIELDS` accepts comma-separated field names. Country priority ordering is applied only to these fields.

### Oppwaa payment provide
You need set up theses variables in the `.env` file of your Laravel application:
```dotenv
# Oppwa Payment Provider
CHECKOUT_WIDGET_OPPWA_ACTIVE=true
CHECKOUT_WIDGET_OPPWA_NAME='Oppwa'
CHECKOUT_WIDGET_OPPWA_ENTITY_ID="*******"
CHECKOUT_WIDGET_OPPWA_TOKEN="*******"
```
This a test card number:
```php
4200000000000091
```
Enter the remaining data as you wish. You will find the other card number for different situations in this link: https://axcessms.docs.oppwa.com/tutorials/threeDSecure/TestingGuide

### Stripe payment provide
You need set up theses variables in the `.env` file of your Laravel application:
```dotenv
# Strip Payment Provider
CHECKOUT_STRIPE_ACTIVE=true
CHECKOUT_STRIPE_NAME='Stripe'
CHECKOUT_STRIPE_SECRET_KEY="your stripe secret key"
```
This a test card number:
```php
4242424242424242
```
Enter the remaining data as you wish. You will find the other card number for different situations in this link: https://docs.stripe.com/testing#cards


### Computop payment provide
You need set up theses variables in the `.env` file of your Laravel application:
```dotenv
# Strip Payment Provider
CHECKOUT_COMPUTOP_ACTIVE=true

# false for production
CHECKOUT_COMPUTOP_TEST_MODE=true

CHECKOUT_COMPUTOP_NAME='Computop'
CHECKOUT_COMPUTOP_USERNAME='your username'
CHECKOUT_COMPUTOP_PASSWORD='your password'
```
This a test card number:
```php
//Mastercard
5555555555554444
```
For payment confirmation, use the challenge code `1234`.
Enter the remaining data as you wish. You will find the other card number for different situations in this link: https://developer.computop.com/display/EN/Test+credit+card

### Adyen payment provider
Adyen is integrated as [Drop-in with the sessions flow](https://docs.adyen.com/online-payments/build-your-integration/sessions-flow?platform=Web&integration=Drop-in&version=6.46.0) using the official [Adyen PHP API library](https://github.com/Adyen/adyen-php-api-library). The library's requests are sent with Laravel's HTTP client, so they are logged like every other API call.

1. The payment page creates a payment session with manual capture and shows the Drop-in.
2. When the payment completed or failed, the Drop-in sends the shopper to the payment result page. Shoppers coming back from a redirect, e.g. 3D Secure, land there too.
3. The payment result page verifies the payment with Adyen, books the itinerary, and then captures the payment, or reverses it when the booking failed.

You need set up theses variables in the `.env` file of your Laravel application:
```dotenv
# Adyen Payment Provider
CHECKOUT_ADYEN_ACTIVE=true
CHECKOUT_ADYEN_NAME='Adyen'

# "test", or the region of the live account: "live", "live-us", "live-au", "live-nea" or "live-in"
CHECKOUT_ADYEN_ENVIRONMENT=test
# Only for live: Customer Area > Developers > API URLs > Prefix
CHECKOUT_ADYEN_LIVE_URL_PREFIX=

# Optional, the payment methods to offer, "scheme" are cards. Leave it empty to offer all payment methods enabled in Adyen.
CHECKOUT_ADYEN_ALLOWED_PAYMENT_METHODS=scheme

# The credentials of each currency: CHECKOUT_ADYEN_{CURRENCY}_... with the ISO 4217 code of the currency.
# Use single quotes, as API keys contain special characters.
CHECKOUT_ADYEN_EUR_MERCHANT_ACCOUNT='your EUR merchant account'
CHECKOUT_ADYEN_EUR_API_KEY='your EUR api key'
CHECKOUT_ADYEN_EUR_CLIENT_KEY='your EUR client key'

CHECKOUT_ADYEN_USD_MERCHANT_ACCOUNT='your USD merchant account'
CHECKOUT_ADYEN_USD_API_KEY='your USD api key'
CHECKOUT_ADYEN_USD_CLIENT_KEY='your USD client key'
```

**Credentials per currency:** a payment always uses the credentials of its currency. To accept another currency, add its three variables, e.g. `CHECKOUT_ADYEN_GBP_MERCHANT_ACCOUNT`, `CHECKOUT_ADYEN_GBP_API_KEY` and `CHECKOUT_ADYEN_GBP_CLIENT_KEY`. No code or config change is needed, as every currency with a `CHECKOUT_ADYEN_{CURRENCY}_MERCHANT_ACCOUNT` variable is picked up. A currency may also set its own `CHECKOUT_ADYEN_{CURRENCY}_ENVIRONMENT` and `CHECKOUT_ADYEN_{CURRENCY}_LIVE_URL_PREFIX`, e.g. when it is processed in another live region.

**Currencies without credentials:** Adyen is offered for every currency. By default, a payment in a currency without credentials throws a `MissingAdyenCredentialsException`, e.g. "No Adyen credentials are configured for GBP or enable using the default credentials." To pay these currencies with default credentials instead, enable them:
```dotenv
CHECKOUT_ADYEN_USE_DEFAULT_CREDENTIALS=true
CHECKOUT_ADYEN_MERCHANT_ACCOUNT='your default merchant account'
CHECKOUT_ADYEN_API_KEY='your default api key'
CHECKOUT_ADYEN_CLIENT_KEY='your default client key'
```
The credentials of a currency and the default credentials are never mixed: when a currency has a merchant account but not all its keys, an exception is thrown either way. A payment also keeps the credentials it was created with until it is captured or reversed, so adding the credentials of a currency does not affect its open payments.

The domain of the checkout must be an allowed origin of every client key (Customer Area > Developers > API credentials).

Only offer payment methods that authorize the payment right away and can be captured separately, as the itinerary is booked as soon as the shopper comes back.

These are test card numbers, with the expiry date `03/2030` and the security code `737`:
```php
// Visa
4111111145551142
// Mastercard
5555341244441115
```
You will find the other card number for different situations in this link: https://docs.adyen.com/development-resources/test-cards-and-credentials/test-card-numbers

## Adding a new payment method
Payment methods are intentionally pluggable. A new provider should be added as a new gateway class without changing the existing production gateways. The checkout discovers providers from the `payment` array in `config/checkout.php`, filters them by `isActive()`, and then uses the implemented contract type to decide whether to redirect the customer or render an embedded widget.

Before adding a provider, choose the integration type:

- Use `RedirectPaymentContract` when the customer leaves the checkout and completes payment on a hosted provider page.
- Use `WidgetPaymentContract` when the provider renders a widget, iframe, script, or form inside the checkout page.

Both contracts extend `PaymentContract`, so every gateway must implement the shared payment lifecycle.

### 1. Create the gateway class
Create a dedicated class under `src/Payments/Gateways/{ProviderName}`. Use the existing gateways as references:

- `src/Payments/Gateways/Invoice/InvoiceGateway.php` for a simple redirect flow.
- `src/Payments/Gateways/Oppwa/OppwaWidgetGateway.php` for a widget flow.
- `src/Payments/Gateways/Computop/ComputopTokenGateway.php` for a tokenized flow.

Example redirect gateway skeleton:

```php
<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\AcmePay;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaPaymentMethodEnum;
use Nezasa\Checkout\Integrations\Nezasa\Enums\NezasaTransactionStatusEnum;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Contracts\RedirectPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

final class AcmePayGateway implements RedirectPaymentContract
{
    public static function isActive(): bool
    {
        return Config::boolean('checkout.integrations.acme_pay.active');
    }

    public static function name(): string
    {
        return Config::string('checkout.integrations.acme_pay.name');
    }

    public static function isTokenized(): bool
    {
        return false;
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        // Create the payment/session at the provider and store only the data needed later.
        return new PaymentInit(
            isAvailable: true,
            returnUrl: $data->returnUrl,
            persistentData: [
                'provider_payment_id' => 'provider-id',
                'redirect_url' => 'https://provider.example.test/pay',
            ],
        );
    }

    public function getRedirectUrl(PaymentInit $init): Uri
    {
        return Uri::of($init->persistentData['redirect_url']);
    }

    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        // Verify the callback/status with the provider.
        return new AuthorizationResult(isSuccessful: true, resultData: $persistentData);
    }

    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        // Capture the authorized amount after the booking succeeds.
        return new CaptureResult(isSuccessful: true, persistentData: $resultData);
    }

    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        // Cancel, void, or reverse the authorization when the booking fails.
        return new AbortResult(isSuccessful: true, persistentData: $resultData);
    }

    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): NezasaPayload
    {
        /** @var Transaction $transaction */
        $transaction = $request->route('transaction');

        return new NezasaPayload(
            externalRefId: $captureResult->persistentData['provider_payment_id'],
            amount: $transaction->price,
            paymentMethod: NezasaPaymentMethodEnum::Other,
            status: NezasaTransactionStatusEnum::Closed,
            paymentMethodName: self::name(),
        );
    }
}
```

For widget payments, implement `WidgetPaymentContract` instead and return a `PaymentAsset` from `getAssets()`:

```php
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;

final class AcmePayWidgetGateway implements WidgetPaymentContract
{
    // Implement all PaymentContract methods...

    public function getAssets(PaymentInit $paymentInit): PaymentAsset
    {
        return new PaymentAsset(
            isAvailable: true,
            scripts: [
                '<script src="https://provider.example.test/widget.js"></script>',
            ],
            html: '<form class="provider-widget"></form>',
        );
    }
}
```

### 2. Implement the lifecycle correctly
The lifecycle is called by the checkout in this order:

1. `prepare(PaymentPrepareData $data)` creates the provider-side checkout/session and returns a `PaymentInit`.
2. `getRedirectUrl()` or `getAssets()` starts the customer-facing payment step.
3. `authorize(Request $request, array $persistentData)` verifies the provider callback/status.
4. `capture(Request $request, array $persistentData, array $resultData)` captures the payment after booking succeeds.
5. `abort(Request $request, array $persistentData, array $resultData)` reverses or cancels the authorization when booking fails.
6. `makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult)` maps the successful payment into a Nezasa transaction payload.

Keep `persistentData` small and stable because it is stored on the transaction and used later by callback handlers. Store provider identifiers, redirect URLs, amounts, and any token/reference needed for authorization, capture, or abort. Do not store secrets.

### 3. Decide whether the gateway is tokenized
Return `true` from `isTokenized()` only when the provider authorizes the card and sends a reusable token or alias to Nezasa, while Nezasa captures the money later. Tokenized gateways must not capture money at the payment provider during `capture()`. See `ComputopTokenGateway` for the current tokenized behavior.

For normal redirect or widget payment methods, return `false`.

### 4. Add configuration
Add provider configuration under `integrations` in `config/checkout.php` and read it through Laravel config helpers inside the gateway:

```php
'integrations' => [
    'acme_pay' => [
        'active' => (bool) env('CHECKOUT_ACME_PAY_ACTIVE', false),
        'name' => env('CHECKOUT_ACME_PAY_NAME', 'Acme Pay'),
        'base_url' => env('CHECKOUT_ACME_PAY_BASE_URL', 'https://api.provider.example.test'),
        'username' => env('CHECKOUT_ACME_PAY_USERNAME', 'must_be_set_in_env'),
        'password' => env('CHECKOUT_ACME_PAY_PASSWORD', 'must_be_set_in_env'),
    ],
],
```

Then add the gateway class to the `payment` list:

```php
'payment' => [
    OppwaWidgetGateway::class,
    InvoiceGateway::class,
    StripeGateway::class,
    ComputopGateway::class,
    ComputopTokenGateway::class,
    AcmePayGateway::class,
],
```

The value returned by `name()` is displayed to the customer and encrypted into the payment selection URL. It must be unique across all payment methods.

### 5. Add tests before enabling the provider
Every new payment method must have tests. At minimum, cover:

- Contract type: the class implements `RedirectPaymentContract` or `WidgetPaymentContract`.
- Activation and naming: `isActive()` and `name()` read the expected config keys.
- Tokenization: `isTokenized()` returns the correct value.
- Preparation: `prepare()` returns an available `PaymentInit` and stores the expected `persistentData`.
- Customer entry point: redirects return the expected `Uri`; widgets return the expected `PaymentAsset`.
- Callback lifecycle: `authorize()`, `capture()`, and `abort()` behave correctly for successful and failed provider responses.
- Nezasa mapping: `makeNezasaTransactionPayload()` maps amount, external reference, payment method, status, and payment method name correctly.
- Provider discovery: the gateway appears through `GetPaymentProviderAction` only when active.

Use Saloon `MockClient` for provider HTTP calls so tests never call real payment services. The existing tests in `tests/Unit/Payments/Gateways/PaymentGatewaysTest.php` are the baseline for current payment behavior and should remain green when a new gateway is added.

### Other configuration options
You can also configure other options like max child age:
```dotenv
# the default value is 17
MAC_CHILD_CHECKOUT_AGE=16
```

### Useful commands:

⚡️ Install the package using [Composer](https://getcomposer.org):
```bash
composer milzer/checkout
```
Then you need to run the following command to publish the migration file:
```bash
php artisan vendor:publish --tag=checkout-migrations
```
And execute the migration:
```bash
php artisan migrate
```

🧹 Keep a modern codebase with **Pint**:
```bash
composer lint
```

✅ Run refactors using **Rector**
```bash
composer refactor
```

⚗️ Run static analysis using **PHPStan**:
```bash
composer test:types
```

✅ Run unit tests using **PEST**
```bash
composer test:unit
```

🚀 Run the entire test suite:
```bash
composer test
```

This package was created by milzer GmbH under the **[MIT license](https://opensource.org/licenses/MIT)**.
