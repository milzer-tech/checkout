<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Adyen\Http;

use Adyen\AdyenException;
use Adyen\Client;
use Adyen\ConnectionException;
use Adyen\HttpClient\ClientInterface;
use Adyen\HttpClient\CurlClient;
use Adyen\Service;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Support\Facades\Http;
use Milzer\HttpLogger\Core\HttpLogger;
use Milzer\HttpLogger\Core\LoggingOptions;
use Milzer\HttpLogger\Guzzle\LogOutgoingRequests;
use Nezasa\Checkout\Support\CheckoutLogContext;

/**
 * Sends the requests of the Adyen API library with Laravel's HTTP client instead of the library's
 * cURL client, so they are logged like every other API call of the checkout and can be faked in tests.
 *
 * It behaves like the library's cURL client: it returns the decoded response and throws an
 * AdyenException when Adyen does not accept the request.
 *
 * @see CurlClient
 */
final class AdyenHttpClient implements ClientInterface
{
    /**
     * Send a POST request.
     *
     * @param  string  $requestUrl
     * @param  array<string, mixed>|null  $params
     * @return array<array-key, mixed>
     *
     * @throws AdyenException
     */
    public function requestJson(Service $service, $requestUrl, $params): array
    {
        return $this->requestHttp($service, $requestUrl, $params, self::HTTP_METHOD_POST);
    }

    /**
     * Send a POST request.
     *
     * @param  string  $requestUrl
     * @param  array<string, mixed>|null  $params
     * @return array<array-key, mixed>
     *
     * @throws AdyenException
     */
    public function requestPost(Service $service, $requestUrl, $params): array
    {
        return $this->requestHttp($service, $requestUrl, $params, self::HTTP_METHOD_POST);
    }

    /**
     * Send a request with the API key of the client the service belongs to.
     *
     * @param  string  $requestUrl
     * @param  array<string, mixed>|null  $params
     * @param  string  $method
     * @param  array{headers?: array<string, string>, idempotencyKey?: string}|null  $requestOptions
     * @return array<array-key, mixed>
     *
     * @throws AdyenException
     */
    public function requestHttp(Service $service, $requestUrl, $params, $method, $requestOptions = null): array
    {
        $client = $service->getClient();
        $config = $client->getConfig();

        $headers = [
            'x-api-key' => $config->getXApiKey(),
            'adyen-library-name' => $client->getLibraryName(),
            'adyen-library-version' => $client->getLibraryVersion(),
            ...($requestOptions['headers'] ?? []),
        ];

        if (filled($requestOptions['idempotencyKey'] ?? null)) {
            $headers['Idempotency-Key'] = $requestOptions['idempotencyKey'];
        }

        try {
            $response = Http::withMiddleware($this->logger($service))
                ->withHeaders($headers)
                ->withUserAgent(Client::USER_AGENT_SUFFIX.$client->getLibraryVersion())
                ->acceptJson()
                ->asJson()
                ->timeout($config->getTimeout() ?? 60)
                ->connectTimeout($config->getConnectionTimeout() ?? 60)
                ->send(strtoupper($method), $requestUrl, $params === null ? [] : ['json' => $params]);
        } catch (HttpConnectionException $exception) {
            throw new ConnectionException("Could not connect to Adyen ($requestUrl): {$exception->getMessage()}", $exception->getCode(), $exception);
        }

        $result = $response->json();

        if (! in_array($response->status(), [200, 201, 202, 204], true)) {
            throw is_array($result) && isset($result['message'], $result['errorCode'])
                ? new AdyenException(
                    $result['message'],
                    $response->status(),
                    null,
                    $result['status'] ?? $response->status(),
                    $result['errorType'] ?? null,
                    $result['pspReference'] ?? null,
                    $result['errorCode'],
                )
                : new AdyenException($response->body(), $response->status());
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Log the requests with the checkout parameters, under the names of the Adyen calls, and with
     * the service of the Adyen library that sends them, like the Saloon integrations log their requests.
     */
    private function logger(Service $service): LogOutgoingRequests
    {
        return HttpLogger::middleware(
            [...CheckoutLogContext::resolve(), 'adyen' => ['service' => $service::class]],
            static fn (LoggingOptions $options): LoggingOptions => $options->withOutgoingMessages(
                request: 'checkout-to-adyen',
                response: 'adyen-to-checkout',
                failure: 'adyen-failed',
            )
        );
    }
}
