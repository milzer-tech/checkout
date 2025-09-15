# Checkout Process for Nezasa Web Application

This Laravel package provides a **complete checkout process** for the Nezasa web application.  
It integrates with **Nezasa’s APIs** to handle all the necessary steps for booking an itinerary, ensuring a smooth and reliable booking experience.

---

## Requirements

Make sure your environment meets the following requirements:

1. **PHP** 8.3 or higher
2. **MySQL** 8
3. **Laravel** 11 or higher

---
## Installation & Setup

This package is already installed and pre-configured in the [checkout-main-app repository](https://github.com/milzer-tech/checkout-main-app).

The repository also includes useful configurations and dependencies to help you set up and run a Laravel application faster:

- **Vite configuration** for asset bundling and front-end builds
- **Laravel Horizon** for managing and monitoring queues

With these configurations in place, you can quickly get started with the Nezasa checkout.

---
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
