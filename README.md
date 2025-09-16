# Checkout Process for Nezasa Web Application

This Laravel package provides a **complete checkout process** for the Nezasa web application.  
It integrates with **Nezasa’s APIs** to handle all the necessary steps for booking an itinerary, ensuring a smooth and reliable booking experience.

---

## Requirements

Make sure your environment meets the following requirements:

1. **PHP** 8.3 or higher
2. **MySQL** 8
3. **Laravel** 11 or higher
4. **Redis (optional)** 

The package relies heavily on **cache** and **queues** to ensure better performance.  
It is **highly recommended** to use **Redis** as both the queue driver and cache driver for optimal speed and reliability.
---
## Installation & Setup

This package is already installed and pre-configured in the [checkout-main-app repository](https://github.com/milzer-tech/checkout-main-app).

The repository also includes useful configurations and dependencies to help you set up and run a Laravel application faster:

- **Vite configuration** for asset bundling and front-end builds
- **Laravel Horizon** for managing and monitoring queues

With these configurations in place, you can quickly get started with the Nezasa checkout.

---

### Features of the Package

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

### Useful commands:

⚡️ Install the package using [Composer](https://getcomposer.org):

```bash
composer milzer/checkout
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
