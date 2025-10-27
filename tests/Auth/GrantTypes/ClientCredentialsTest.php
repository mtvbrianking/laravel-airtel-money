<?php

namespace Bmatovu\AirtelMoney\Tests\Auth\GrantTypes;

use Bmatovu\AirtelMoney\Auth\GrantTypes\ClientCredentials;
use Bmatovu\AirtelMoney\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ClientCredentialsTest extends TestCase
{
    public function test_throws_exception_for_missing_args(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $missing = ['client_id', 'client_secret', 'token_uri'];

        $message = 'Parameters: '.implode(', ', $missing).' are required.';

        $this->expectExceptionMessage($message);

        new ClientCredentials(new Client, []);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function test_can_obtain_new_access_token(): void
    {
        $api_response = [
            'access_token' => '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3',
            'refresh_token' => '7yWd6bgLij5AkeuBQs0hx2EDDcCpXYTUkDVhEZQK8MagOuIuKw',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        // Create a mock and queue two responses.
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($api_response)),
            new Response(401, [], null),
            new RequestException('Error Communicating with Server', new Request('GET', 'last')),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        // Add the history middleware to the handler stack.
        $handlerStack->push($historyMiddleware);

        $client = new Client([
            'base_uri' => 'http://localhost:8000/',
            'handler' => $handlerStack,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $client_id = 'bd3sL3Cx2j';
        $client_secret = 'On8dC2YE7LHwo0fwqOQH';

        $grantType = new ClientCredentials($client, [
            'token_uri' => 'oauth/token',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'user_login,user_registration',
        ]);

        $api_token = $grantType->getToken();

        $this->assertNotEmpty($historyContainer);
        $request = $historyContainer[0]['request'];
        $api_request_authorization_header = $request->getHeader('Authorization')[0];
        $api_request_body = json_decode($request->getBody(), true);

        $this->assertEquals(base64_encode($client_id.':'.$client_secret), ltrim($api_request_authorization_header, 'Basic '));
        $this->assertEquals('client_credentials', $api_request_body['grant_type']);
        $this->assertEquals('user_login,user_registration', $api_request_body['scope']);
        $this->assertEquals($api_response, $api_token);
    }
}
