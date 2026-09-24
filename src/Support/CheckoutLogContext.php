<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Livewire\Livewire;

/**
 * Resolves the checkout query parameters that are attached to every outgoing HTTP log entry.
 */
final class CheckoutLogContext
{
    /**
     * The hidden Laravel Context key that carries the parameters into queued jobs.
     */
    public const CONTEXT_KEY = 'checkout_log_context';

    /**
     * The log keys (which are also the Livewire property names) mapped to their query-string names.
     */
    private const PARAMS = [
        'checkoutId' => 'checkoutId',
        'itineraryId' => 'itineraryId',
        'origin' => 'origin',
        'lang' => 'lang',
        'restPayment' => 'rest-payment',
    ];

    /**
     * Get the checkout parameters of the current request or queued job.
     *
     * @return array<string, string|bool>
     */
    public static function resolve(): array
    {
        $params = Context::getHidden(self::CONTEXT_KEY);

        return is_array($params) ? $params : self::fromRequest(request());
    }

    /**
     * Get the checkout parameters from the query string, or from the component snapshot on Livewire update requests.
     *
     * @return array<string, string|bool>
     */
    public static function fromRequest(Request $request): array
    {
        $params = [];

        foreach (self::PARAMS as $key => $queryName) {
            $params[$key] = $request->query($queryName);
        }

        $params = self::normalize($params);

        if (! isset($params['checkoutId']) && Livewire::isLivewireRequest()) {
            return self::fromLivewireSnapshot($request);
        }

        return $params;
    }

    /**
     * Get the checkout parameters from the first Livewire component snapshot that holds them.
     *
     * @return array<string, string|bool>
     */
    private static function fromLivewireSnapshot(Request $request): array
    {
        foreach ((array) $request->input('components', []) as $component) {
            $snapshot = json_decode((string) data_get($component, 'snapshot'), true);
            $data = data_get($snapshot, 'data');

            if (is_array($data) && isset($data['checkoutId'])) {
                $params = [];

                foreach (array_keys(self::PARAMS) as $key) {
                    $params[$key] = $data[$key] ?? null;
                }

                return self::normalize($params);
            }
        }

        return [];
    }

    /**
     * Drop missing values and cast the rest payment flag to a boolean.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, string|bool>
     */
    private static function normalize(array $params): array
    {
        $params = array_filter($params, static fn (mixed $value): bool => is_scalar($value) && $value !== '');

        if (array_key_exists('restPayment', $params)) {
            $params['restPayment'] = filter_var($params['restPayment'], FILTER_VALIDATE_BOOLEAN);
        }

        return array_map(static fn (mixed $value): string|bool => is_bool($value) ? $value : (string) $value, $params);
    }
}
