<?php

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Nezasa\Checkout\Providers\CheckoutServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Saloon\Http\Faking\MockClient;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Fixtures\NoViteServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use LazilyRefreshDatabase;
    use MatchesSnapshots;

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
            LivewireServiceProvider::class,
            LaravelDataServiceProvider::class,
            CheckoutServiceProvider::class,
            NoViteServiceProvider::class,
        ];
    }

    /**
     * Define environment setup for tests.
     * This configures an in-memory SQLite database so all tests run against a transient DB.
     */
    #[\Override]
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    /**
     * Clean up the testing environment before the next test.
     */
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        if (! Schema::hasTable('checkouts')) {
            $migration = require __DIR__.'/../database/migrations/create_checkout_table.php';

            $migration->up();
        }
    }

    /**
     * Get the snapshot directory for the current test class.
     */
    protected function getSnapshotDirectory(): string
    {
        return __DIR__.'/'.str(static::class)
            ->after('P\Tests\\')
            ->replace('\\', '/')
            ->beforeLast('/')
            ->append('/__snapshots__');
    }
}
