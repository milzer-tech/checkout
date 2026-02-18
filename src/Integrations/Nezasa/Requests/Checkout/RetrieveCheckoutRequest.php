<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Requests\Checkout;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Exceptions\NotFoundException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\RetrieveCheckoutResponse;
use Saloon\Enums\Method;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Http\Faking\FakeResponse;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class RetrieveCheckoutRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Create a new instance of RetrieveCheckoutRequest
     */
    public function __construct(protected readonly string $checkoutId) {}

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/checkout/v1/checkouts/'.$this->checkoutId;
    }

    /**
     * Cast the response to a DTO.
     *
     * @throws Throwable
     */
    public function createDtoFromResponse(Response $response): RetrieveCheckoutResponse
    {
        throw_unless(condition: $response->ok(), exception: NotFoundException::class);

        return RetrieveCheckoutResponse::from($response->array());
    }

    //    public function middleware(): MiddlewarePipeline
    //    {
    //        if (! Config::boolean('checkout.fake_calls')) {
    //            return parent::middleware();
    //        }
    //
    //        $directory = checkout_path('tests/Fixtures/Saloon/checkout/'.request()->input('checkoutId'));
    //        $file = "$directory/".class_basename(static::class).'.json';
    //
    //        return parent::middleware()
    //            ->onRequest(function (PendingRequest $pendingRequest) use ($file) {
    //                if (! file_exists($file)) {
    //                    return;
    //                }
    //
    //                $file = file_get_contents($file);
    //                $data = json_decode($file, true);
    //
    //                return new FakeResponse($data['data'], $data['statusCode'], $data['headers']);
    //            })
    //            ->onResponse(function (Response $response) use ($directory, $file) {
    //                if (! is_dir($directory)) {
    //                    mkdir($directory, 0755, true); // true = create nested directories
    //                }
    //
    //                file_put_contents($file, json_encode([
    //                    'data' => $response->array(),
    //                    'statusCode' => $response->status(),
    //                    'headers' => $response->headers()->all(),
    //                ])
    //                );
    //            });
    //    }
}
