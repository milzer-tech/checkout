<?php

namespace Tests;

use Nezasa\Checkout\Providers\CheckoutServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\MockConfig;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     *
     * @api
     */
    #[\Override]
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
            CheckoutServiceProvider::class,
        ];
    }

    /**
     * Clean up the testing environment before the next test.
     */
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        MockConfig::setFixturePath(__DIR__.'/'.str(static::class)
            ->after('P\Tests\\')
            ->replace('\\', '/')
            ->beforeLast('/')
            ->append('/saloon_responses/')
        );
    }
}
