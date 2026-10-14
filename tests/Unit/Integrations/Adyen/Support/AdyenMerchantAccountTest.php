<?php

declare(strict_types=1);

use Adyen\Environment;
use Adyen\Service\Checkout\PaymentsApi;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Adyen\Exceptions\MissingAdyenCredentialsException;
use Nezasa\Checkout\Integrations\Adyen\Http\AdyenHttpClient;
use Nezasa\Checkout\Integrations\Adyen\Support\AdyenMerchantAccount;

/**
 * Set environment variables like a .env file or the server would.
 *
 * @param  array<string, string>  $variables
 * @param  array<int, string>  $sources
 */
function setAdyenEnvironment(array $variables, array $sources = ['putenv', 'env', 'server']): void
{
    foreach ($variables as $name => $value) {
        if (in_array('putenv', $sources, true)) {
            putenv("$name=$value");
        }

        if (in_array('env', $sources, true)) {
            $_ENV[$name] = $value;
        }

        if (in_array('server', $sources, true)) {
            $_SERVER[$name] = $value;
        }
    }
}

/**
 * Load the Adyen config of the package config file.
 *
 * @return array<string, mixed>
 */
function loadAdyenConfigFile(): array
{
    return (require dirname(__DIR__, 5).'/config/checkout.php')['integrations']['adyen'];
}

/**
 * Remove the CHECKOUT_ADYEN_* variables, so the tests do not depend on the environment they run in.
 */
function clearAdyenEnvironment(): void
{
    foreach (array_keys([...getenv(), ...$_ENV, ...$_SERVER]) as $name) {
        if (is_string($name) && str_starts_with($name, 'CHECKOUT_ADYEN_')) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }
}

beforeEach(function (): void {
    clearAdyenEnvironment();
    configureAdyen();
});

afterEach(function (): void {
    clearAdyenEnvironment();
});

it('uses the credentials of the currency', function (string $currency, string $merchantAccount, string $apiKey, string $clientKey): void {
    $account = AdyenMerchantAccount::forCurrency($currency);

    expect($account->currency)->toBe(strtoupper(trim($currency)))
        ->and($account->credentials)->toBe(strtoupper(trim($currency)))
        ->and($account->merchantAccount)->toBe($merchantAccount)
        ->and($account->apiKey)->toBe($apiKey)
        ->and($account->clientKey)->toBe($clientKey)
        ->and($account->environment)->toBe('test')
        ->and($account->liveUrlPrefix)->toBeNull()
        ->and($account->isLive())->toBeFalse();
})->with([
    'EUR' => ['EUR', 'MerchantEUR', 'eur-api-key', 'test_eur-client-key'],
    'USD' => ['USD', 'MerchantUSD', 'usd-api-key', 'test_usd-client-key'],
    'lowercase with spaces' => [' usd ', 'MerchantUSD', 'usd-api-key', 'test_usd-client-key'],
]);

it('lets a currency override the environment and live URL prefix', function (): void {
    Config::set('checkout.integrations.adyen.live_url_prefix', 'shared-prefix');
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.environment', 'live-us');
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.live_url_prefix', 'usd-prefix');

    $usd = AdyenMerchantAccount::forCurrency('USD');
    $eur = AdyenMerchantAccount::forCurrency('EUR');

    expect($usd->environment)->toBe('live-us')
        ->and($usd->liveUrlPrefix)->toBe('usd-prefix')
        ->and($usd->isLive())->toBeTrue()
        ->and($eur->environment)->toBe('test')
        ->and($eur->liveUrlPrefix)->toBe('shared-prefix');
});

it('throws an exception for a currency without credentials', function (): void {
    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCurrency('gbp'))->toThrow(
        MissingAdyenCredentialsException::class,
        'No Adyen credentials are configured for GBP or enable using the default credentials.'
    );
});

it('treats a currency with an empty merchant account as a currency without credentials', function (): void {
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.merchant_account', '');

    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCurrency('USD'))
        ->toThrow(MissingAdyenCredentialsException::class, 'No Adyen credentials are configured for USD or enable using the default credentials.');
});

it('uses the default credentials for a currency without credentials when enabled', function (): void {
    Config::set('checkout.integrations.adyen.use_default_credentials', true);

    $account = AdyenMerchantAccount::forCurrency('GBP');

    expect($account->currency)->toBe('GBP')
        ->and($account->credentials)->toBe('default')
        ->and($account->merchantAccount)->toBe('MerchantDefault')
        ->and($account->apiKey)->toBe('default-api-key')
        ->and($account->clientKey)->toBe('test_default-client-key')
        ->and($account->environment)->toBe('test')
        ->and(AdyenMerchantAccount::forCurrency('EUR')->apiKey)->toBe('eur-api-key');
});

it('throws an exception when the default credentials are incomplete', function (): void {
    Config::set('checkout.integrations.adyen.use_default_credentials', true);
    Config::set('checkout.integrations.adyen.default', ['merchant_account' => 'MerchantDefault', 'api_key' => null, 'client_key' => '']);

    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCurrency('GBP'))->toThrow(
        MissingAdyenCredentialsException::class,
        'The default Adyen credentials are incomplete.'
    );
});

it('never completes the credentials of a currency with the default credentials', function (bool $useDefaultCredentials): void {
    Config::set('checkout.integrations.adyen.use_default_credentials', $useDefaultCredentials);
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.api_key');

    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCurrency('USD'))->toThrow(
        MissingAdyenCredentialsException::class,
        'The Adyen credentials of USD are incomplete.'
    );
})->with([
    'default credentials disabled' => [false],
    'default credentials enabled' => [true],
]);

it('loads the credentials a payment was created with, whatever the currency resolves to now', function (): void {
    $default = AdyenMerchantAccount::forCredentials('default', 'usd');
    $eur = AdyenMerchantAccount::forCredentials('EUR', 'EUR');

    expect($default->currency)->toBe('USD')
        ->and($default->credentials)->toBe('default')
        ->and($default->merchantAccount)->toBe('MerchantDefault')
        ->and($default->apiKey)->toBe('default-api-key')
        ->and($eur->merchantAccount)->toBe('MerchantEUR')
        ->and($eur->apiKey)->toBe('eur-api-key');
});

it('throws an exception when the credentials a payment was created with are gone', function (): void {
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.merchant_account');

    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCredentials('USD', 'USD'))
        ->toThrow(MissingAdyenCredentialsException::class, 'No Adyen credentials are configured for USD or enable using the default credentials.');
});

it('does not show any configuration value in its exceptions', function (): void {
    Config::set('checkout.integrations.adyen.merchant_accounts.USD.api_key');
    Config::set('checkout.integrations.adyen.use_default_credentials', true);
    Config::set('checkout.integrations.adyen.default.client_key');

    $messages = [];

    foreach (['USD', 'GBP', 'CHF'] as $currency) {
        try {
            AdyenMerchantAccount::forCurrency($currency);
        } catch (MissingAdyenCredentialsException $exception) {
            $messages[] = $exception->getMessage();
        }
    }

    expect($messages)->toBe([
        'The Adyen credentials of USD are incomplete.',
        'The default Adyen credentials are incomplete.',
        'The default Adyen credentials are incomplete.',
    ]);
});

it('removes the whitespace and invisible characters a copied value brings along', function (string $around): void {
    $apiKey = 'AQEabc+def/ghi=-JKL=-m1n.o;$9P]Q4R,@S';

    Config::set('checkout.integrations.adyen.merchant_accounts.EUR', [
        'merchant_account' => $around.'MerchantEUR'.$around,
        'api_key' => $around.$apiKey.$around,
        'client_key' => $around.'test_CLIENTKEY'.$around,
    ]);

    $account = AdyenMerchantAccount::forCurrency('EUR');

    expect($account->apiKey)->toBe($apiKey)
        ->and($account->clientKey)->toBe('test_CLIENTKEY')
        ->and($account->merchantAccount)->toBe('MerchantEUR');
})->with([
    'line separator' => ["\u{2028}"],
    'paragraph separator' => ["\u{2029}"],
    'zero-width space' => ["\u{200B}"],
    'byte order mark' => ["\u{FEFF}"],
    'no-break space' => ["\u{00A0}"],
    'line break and tab' => ["\r\n\t "],
]);

it('treats a value of only invisible characters as not set', function (): void {
    Config::set('checkout.integrations.adyen.merchant_accounts.EUR.api_key', "\u{2028}\u{200B} ");

    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCurrency('EUR'))
        ->toThrow(MissingAdyenCredentialsException::class, 'The Adyen credentials of EUR are incomplete.');
});

it('reads the credentials of every currency from its environment variables', function (): void {
    setAdyenEnvironment([
        'CHECKOUT_ADYEN_GBP_MERCHANT_ACCOUNT' => 'MerchantGBP',
        'CHECKOUT_ADYEN_GBP_API_KEY' => 'gbp-api-key',
        'CHECKOUT_ADYEN_GBP_CLIENT_KEY' => 'test_gbp-client-key',
        'CHECKOUT_ADYEN_JOD_MERCHANT_ACCOUNT' => 'MerchantJOD',
        'CHECKOUT_ADYEN_JOD_API_KEY' => 'jod-api-key',
        'CHECKOUT_ADYEN_JOD_CLIENT_KEY' => 'test_jod-client-key',
        'CHECKOUT_ADYEN_JOD_ENVIRONMENT' => 'live',
        'CHECKOUT_ADYEN_JOD_LIVE_URL_PREFIX' => 'jod-prefix',
        'CHECKOUT_ADYEN_MERCHANT_ACCOUNT' => 'MerchantDefault',
        'CHECKOUT_ADYEN_API_KEY' => 'default-api-key',
        'CHECKOUT_ADYEN_CLIENT_KEY' => 'test_default-client-key',
        'CHECKOUT_ADYEN_USE_DEFAULT_CREDENTIALS' => 'true',
        // Not the variables of a currency.
        'CHECKOUT_ADYEN_EURO_MERCHANT_ACCOUNT' => 'MerchantEURO',
        'CHECKOUT_ADYEN_eur_MERCHANT_ACCOUNT' => 'MerchantLowercase',
    ]);

    $config = loadAdyenConfigFile();

    expect($config['merchant_accounts'])->toBe([
        'GBP' => [
            'merchant_account' => 'MerchantGBP',
            'api_key' => 'gbp-api-key',
            'client_key' => 'test_gbp-client-key',
            'environment' => null,
            'live_url_prefix' => null,
        ],
        'JOD' => [
            'merchant_account' => 'MerchantJOD',
            'api_key' => 'jod-api-key',
            'client_key' => 'test_jod-client-key',
            'environment' => 'live',
            'live_url_prefix' => 'jod-prefix',
        ],
    ])
        ->and($config['use_default_credentials'])->toBeTrue()
        ->and($config['default'])->toBe([
            'merchant_account' => 'MerchantDefault',
            'api_key' => 'default-api-key',
            'client_key' => 'test_default-client-key',
        ]);

    Config::set('checkout.integrations.adyen', $config);

    expect(AdyenMerchantAccount::forCurrency('GBP')->apiKey)->toBe('gbp-api-key')
        ->and(AdyenMerchantAccount::forCurrency('JOD')->liveUrlPrefix)->toBe('jod-prefix')
        ->and(AdyenMerchantAccount::forCurrency('CHF')->merchantAccount)->toBe('MerchantDefault');
});

it('reads the variables of a currency wherever the environment provides them', function (string $source): void {
    setAdyenEnvironment([
        'CHECKOUT_ADYEN_CHF_MERCHANT_ACCOUNT' => 'MerchantCHF',
        'CHECKOUT_ADYEN_CHF_API_KEY' => 'chf-api-key',
        'CHECKOUT_ADYEN_CHF_CLIENT_KEY' => 'test_chf-client-key',
    ], [$source]);

    expect(loadAdyenConfigFile()['merchant_accounts']['CHF']['api_key'] ?? null)->toBe('chf-api-key');
})->with([
    'server environment' => ['putenv'],
    '.env file' => ['env'],
    'server variables' => ['server'],
]);

it('throws an exception by default for a currency without environment variables', function (): void {
    Config::set('checkout.integrations.adyen', loadAdyenConfigFile());

    expect(fn (): AdyenMerchantAccount => AdyenMerchantAccount::forCurrency('EUR'))
        ->toThrow(MissingAdyenCredentialsException::class, 'No Adyen credentials are configured for EUR or enable using the default credentials.');
});

it('creates a test client of the Adyen library that sends its requests through the logged HTTP client', function (): void {
    $client = AdyenMerchantAccount::forCurrency('USD')->client();

    expect($client->getConfig()->getXApiKey())->toBe('usd-api-key')
        ->and($client->getConfig()->getEnvironment())->toBe(Environment::TEST)
        ->and($client->getConfig()->getTimeout())->toBe(60)
        ->and($client->getConfig()->getConnectionTimeout())->toBe(60)
        ->and($client->getHttpClient())->toBeInstanceOf(AdyenHttpClient::class);
});

it('creates a live client that uses the live endpoint of the prefix', function (): void {
    Config::set('checkout.integrations.adyen.environment', 'live');
    Config::set('checkout.integrations.adyen.live_url_prefix', 'shared-prefix');

    $client = AdyenMerchantAccount::forCurrency('EUR')->client();

    expect($client->getConfig()->getEnvironment())->toBe(Environment::LIVE)
        ->and($client->getConfig()->get('prefix'))->toBe('shared-prefix')
        ->and((new ReflectionProperty(PaymentsApi::class, 'baseURL'))->getValue(new PaymentsApi($client)))
        ->toBe('https://shared-prefix-checkout-live.adyenpayments.com/checkout/v71');
});
