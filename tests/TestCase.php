<?php

namespace Tests;

use Illuminate\Support\Carbon;
use Nezasa\Checkout\Providers\CheckoutServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\MockConfig;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\Snapshots\MatchesSnapshots;

abstract class TestCase extends OrchestraTestCase
{
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

        MockConfig::setFixturePath(__DIR__.'/'.str(get_class($this))
            ->after('P\Tests\\')
            ->replace('\\', '/')
            ->beforeLast('/')
            ->append('/saloon_responses/')
        );
    }

    /**
     * Get the snapshot directory for the current test class.
     */
    protected function getSnapshotDirectory(): string
    {
        return __DIR__.'/'.str(get_class($this))
            ->after('P\Tests\\')
            ->replace('\\', '/')
            ->beforeLast('/')
            ->append('/__snapshots__');
    }

    /**
     * Set a fake Carbon datetime for testing purposes.
     */
    protected function fakeCarbon(
        int $year = 2025,
        int $month = 5,
        int $day = 26,
        int $hour = 11,
        int $minute = 20,
        int $second = 19
    ): void {
        Carbon::setTestNow(
            Carbon::create($year, $month, $day, $hour, $minute, $second)
        );
    }
}
