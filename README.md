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
12. **Save Booking State** – Preserves the current state of the booking process. For example, if a user leaves the page while entering traveler details, all entered data is stored and restored the next time they return.
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
