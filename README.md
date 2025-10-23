# Checkout Process for Nezasa Web Application

This Laravel package provides a **complete checkout process** for the Nezasa web application.  
It integrates with **Nezasa’s APIs** to handle all the necessary steps for booking an itinerary, ensuring a smooth and reliable booking experience.


## Requirements
Make sure your environment meets the following requirements:

1. **PHP** 8.3 or higher
2. **MySQL** 8
3. **Laravel** 11 or higher
4. **Redis (optional)** 

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

# Oppwa Payment Provider
CHECKOUT_WIDGET_OPPWA_ENTITY_ID="*******"
CHECKOUT_WIDGET_OPPWA_TOKEN="*******"
CHECKOUT_WIDGET_OPPWA_ACTIVE=true
```

## New payment method
One of the main goal of this package is to make it easy to add new payment methods. You need to create two classes and implement the related interfaces for a new payment method. Payment is usually done in two steps:
1. Initiate payment: you need define this interface:
```php
<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

interface WidgetPaymentInitiation
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool;

    /**
     * Returns the name of the payment gateway.
     */
    public static function name(): string;

    /**
     * Returns the description of the payment gateway.
     * if null, no description will be shown.
     */
    public static function description(): ?string;

    /**
     * Prepares the payment initiation process.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit;

    /**
     * Returns the assets required for the payment initiation process.
     */
    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset;

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function getNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload;
}

```
The package uses these methods to initiate payment by calling the payment gateway, display the payment form, and create a transaction in Nezasa.
2. Confirm payment: you need implement this interface:
```php
declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;

interface WidgetPaymentCallBack
{
    /**
     * Handles the callback from the payment gateway.
     *
     * @param  array<string, mixed>|BaseDto  $persistentData
     */
    public function check(Request $request, array|BaseDto $persistentData): PaymentResult;

    /**
     * Shows the result of the payment process to the user.
     */
    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput;
}
```
Then, the package will call the check method to confirm the payment, and save the result in Nezasa.

After defining the classes, you need to add them to the `config/checkout.php` file:
```bash
   'payment' => [
        'widget' => [
            OppwaInitiationWidget::class => OppwaCallBackWidget::class,
            'your custom initiation class' => 'your custom callback class'
        ],
    ],
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
