<?php

namespace Bmatovu\AirtelMoney\Tests;

use Bmatovu\AirtelMoney\AirtelMoneyServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setup();

        fopen(base_path('.env'), 'w');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();

        unlink(base_path('.env'));
    }

    /**
     * Add package service provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AirtelMoneyServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function mockGuzzleClient(mixed $response): ClientInterface
    {
        if (is_array($response)) {
            $responses = $response;
        } else {
            $responses[] = $response;
        }

        $mockHandler = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mockHandler);

        return new Client([
            'base_uri' => 'http://api.example.com/airtel-money/',
            'handler' => $handlerStack,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
