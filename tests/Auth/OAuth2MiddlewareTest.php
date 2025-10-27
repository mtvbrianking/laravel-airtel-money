<?php

namespace Bmatovu\AirtelMoney\Tests\Auth;

use Bmatovu\AirtelMoney\Auth\GrantTypes\ClientCredentials;
use Bmatovu\AirtelMoney\Auth\GrantTypes\RefreshToken;
use Bmatovu\AirtelMoney\Auth\OAuth2Middleware;
use Bmatovu\AirtelMoney\Auth\Repositories\TokenRepository;
use Bmatovu\AirtelMoney\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Psr\Http\Message\RequestInterface;

class OAuth2MiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    protected function extractAccessToken(RequestInterface $request): string
    {
        if (! $request->hasHeader('Authorization')) {
            return '';
        }

        $authHeader = $request->getHeader('Authorization')[0];

        $authHeaderParts = explode(' ', $authHeader);

        return end($authHeaderParts);
    }

    protected function buildSuccessOauthMiddleware(
        string $access_token,
        string $refresh_token = '',
        string $token_type = 'Bearer',
        int $expires_in = 3600
    ): OAuth2Middleware {
        $apiResponse = [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => $token_type,
            'expires_in' => $expires_in,
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($apiResponse)),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($historyMiddleware);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/',
            'headers' => $headers,
        ]);

        $config = [
            'token_uri' => 'oauth/token',
            'client_id' => 'fa5cc82b-6be5-41a4-be48-255fa2aae62b',
            'client_secret' => '3a4f0716-8216-4d2b-a526-3d001dec4832',
        ];

        $clientCredentialsGrant = new ClientCredentials($client, $config);

        $refreshTokenGrant = ! $refresh_token ? null : new RefreshToken($client, $config);

        $tokenRepository = new TokenRepository;

        return new OAuth2Middleware($clientCredentialsGrant, $refreshTokenGrant, $tokenRepository);
    }

    protected function buildFailureOauthMiddleware(): OAuth2Middleware
    {
        $apiResponse = [
            'error' => 'For some reason, you don\'t qualify for a token. Sorry',
        ];

        $mockHandler = new MockHandler([
            new Response(401, [], json_encode($apiResponse)),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($historyMiddleware);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/',
            'headers' => $headers,
        ]);

        $config = [
            'token_uri' => 'oauth/token',
            'client_id' => 'fa5cc82b-6be5-41a4-be48-255fa2aae62b',
            'client_secret' => '3a4f0761-8216-4d2b-a526-3d001dec4832',
        ];

        $clientCredentialsGrant = new ClientCredentials($client, $config);

        $refreshTokenGrant = null;

        $tokenRepository = new TokenRepository;

        return new OAuth2Middleware($clientCredentialsGrant, $refreshTokenGrant, $tokenRepository);
    }

    // public function test_can_instantiate_oauth2_middleware(): void
    // {
    //     $client_grant_stub = $this->getMockBuilder(ClientCredentials::class)
    //         ->disableOriginalConstructor()
    //         ->getMock();

    //     $oauth_mw = new OAuth2Middleware($client_grant_stub);

    //     $this->assertInstanceOf(OAuth2Middleware::class, $oauth_mw);
    // }

    public function test_uses_provided_access_token(): void
    {
        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken);

        // ...........................................................

        $apiResponse = [
            'message' => 'some random data',
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($apiResponse)),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $userChosenAccessToken = 'QBKNcn10frGUSlrbzE17ngD5W1f8L8dcMNPMZGD4V7NDj4CGws';

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$userChosenAccessToken,
            ],
        ]);

        $response = $client->request('GET', 'resource');

        $this->assertNotEmpty($historyContainer);
        $request = $historyContainer[0]['request'];
        $usedAccessToken = $this->extractAccessToken($request);

        $this->assertNotEquals($accessToken, $usedAccessToken);
        $this->assertEquals($userChosenAccessToken, $usedAccessToken);
        $this->assertEquals(json_decode($response->getBody(), true), $apiResponse);
    }

    public function test_throws_exception_if_cant_obtain_new_access_token(): void
    {
        $oauthMiddleware = $this->buildFailureOauthMiddleware();

        $handlerStack = HandlerStack::create();

        $handlerStack->push($oauthMiddleware);

        $this->expectException(ClientException::class);
        // $this->expectExceptionMessage('Unable to obtain a new access token');

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
        ]);

        $client->request('GET', 'resource');
    }

    public function test_can_request_new_token(): void
    {
        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken);

        // ...........................................................

        $apiResponse = [
            'message' => 'some random data',
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($apiResponse)),
        ]);

        $historyContainer = [];

        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = $client->request('GET', 'resource');

        $this->assertNotEmpty($historyContainer);
        $request = $historyContainer[0]['request'];
        $usedAccessToken = $this->extractAccessToken($request);

        $this->assertEquals($accessToken, $usedAccessToken);
        $this->assertEquals(json_decode($response->getBody(), true), $apiResponse);
    }

    public function test_can_refresh_existing_expired_token(): void
    {
        $tokenRepo = new TokenRepository;

        $existingAccessToken = '6OQUFgtm1WgFwTpTK3Snl0qfOLbvAWwKGKTshsdxX0nI1NX4oQ';

        $tokenRepo->create([
            'access_token' => $existingAccessToken,
            'refresh_token' => '7yWd6bgLij5AkeuBQs0hx2EDDcCpXYTUkDVhEZQK8MagOuIuKw',
            'token_type' => 'Bearer',
            'expires_in' => -3600,
        ]);

        // ...........................................................

        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';
        $refreshToken = '7yWd6bgLij5AkeuBQs0hx2EDDcCpXYTUkDVhEZQK8MagOuIuKw';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken, $refreshToken);

        // ...........................................................

        $apiResponse = [
            'message' => 'some random data',
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($apiResponse)),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = $client->request('GET', 'resource');

        $this->assertNotEmpty($historyContainer);
        $request = $historyContainer[0]['request'];
        $usedAccessToken = $this->extractAccessToken($request);

        $this->assertEquals($accessToken, $usedAccessToken);
        $this->assertNotEquals($existingAccessToken, $usedAccessToken);
        $this->assertEquals(json_decode($response->getBody(), true), $apiResponse);
    }

    public function test_can_use_exiting_valid_token(): void
    {
        $tokenRepo = new TokenRepository;

        $existingAccessToken = '6OQUFgtm1WgFwTpTK3Snl0qfOLbvAWwKGKTshsdxX0nI1NX4oQ';

        $tokenRepo->create([
            'access_token' => $existingAccessToken,
            'refresh_token' => 'U51GH5zfLm1tshcNEp7HNvGs0vlgXmODfdEYWoFNc9jBa04iBd',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        // ...........................................................

        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken);

        // ...........................................................

        $apiResponse = [
            'message' => 'some random data',
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($apiResponse)),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = $client->request('GET', 'resource');

        $this->assertNotEmpty($historyContainer);
        $request = $historyContainer[0]['request'];
        $usedAccessToken = $this->extractAccessToken($request);

        $this->assertNotEquals($accessToken, $usedAccessToken);
        $this->assertEquals($existingAccessToken, $usedAccessToken);
        $this->assertEquals(json_decode($response->getBody(), true), $apiResponse);
    }

    public function test_can_retry_request(): void
    {
        $tokenRepo = new TokenRepository;

        $existingAccessToken = '6OQUFgtm1WgFwTpTK3Snl0qfOLbvAWwKGKTshsdxX0nI1NX4oQ';

        $tokenRepo->create([
            'access_token' => $existingAccessToken,
            'refresh_token' => 'U51GH5zfLm1tshcNEp7HNvGs0vlgXmODfdEYWoFNc9jBa04iBd',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        // ...........................................................

        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken);

        // ...........................................................

        $secondResponse = [
            'message' => 'some random data',
        ];

        $mockHandler = new MockHandler([
            new Response(401, [], json_encode(['message' => 'Access token is revoked'])),
            new Response(200, [], json_encode($secondResponse)),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $finalResponse = $client->request('GET', 'resource');

        $this->assertEquals(2, count($historyContainer));

        // First request...

        $firstRequest = $historyContainer[0]['request'];
        $firstResponse = $historyContainer[0]['response'];
        $usedAccessToken = $this->extractAccessToken($firstRequest);

        $this->assertNotEquals($accessToken, $usedAccessToken);
        $this->assertEquals($existingAccessToken, $usedAccessToken);
        $this->assertFalse($firstRequest->hasHeader('X-Guzzle-Retry'));
        $this->assertEquals(401, $firstResponse->getStatusCode());

        // Second request...

        $secondRequest = $historyContainer[1]['request'];
        $secondResponse = $historyContainer[1]['response'];
        $usedAccessToken = $this->extractAccessToken($secondRequest);

        $this->assertNotEquals($existingAccessToken, $usedAccessToken);
        $this->assertEquals('0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3', $usedAccessToken);
        $this->assertTrue($secondRequest->hasHeader('X-Guzzle-Retry'));
        $this->assertEquals('1', $secondRequest->getHeader('X-Guzzle-Retry')[0]);
        $this->assertEquals(200, $secondResponse->getStatusCode());
    }

    public function test_retries_requests_only_once(): void
    {
        $tokenRepo = new TokenRepository;

        $existingAccessToken = '6OQUFgtm1WgFwTpTK3Snl0qfOLbvAWwKGKTshsdxX0nI1NX4oQ';

        $tokenRepo->create([
            'access_token' => $existingAccessToken,
            'refresh_token' => 'U51GH5zfLm1tshcNEp7HNvGs0vlgXmODfdEYWoFNc9jBa04iBd',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        // ...........................................................

        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken);

        // ...........................................................

        $mockHandler = new MockHandler([
            new Response(401, [], json_encode(['message' => 'Client application is blocked.'])),
            new Response(200, [], json_encode(['message' => 'Some random data'])),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Guzzle-Retry' => '1',
            ],
        ]);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Client application is blocked.');

        try {
            $client->request('GET', 'resource');
        } finally {
            $this->assertEquals(1, count($historyContainer));
            $request = $historyContainer[0]['request'];
            $response = $historyContainer[0]['response'];
            $usedAccessToken = $this->extractAccessToken($request);

            $this->assertNotEquals($accessToken, $usedAccessToken);
            $this->assertEquals($existingAccessToken, $usedAccessToken);
            $this->assertTrue($request->hasHeader('X-Guzzle-Retry'));
            $this->assertEquals('1', $request->getHeader('X-Guzzle-Retry')[0]);
            $this->assertEquals(401, $response->getStatusCode());
        }
    }

    public function test_throws_guzzle_exception_on_rejection(): void
    {
        $accessToken = '0wzIjZyzFilj0HWomm4Z6790xezQi5V6skFz81YB99IXHu9RE3';

        $oauthMiddleware = $this->buildSuccessOauthMiddleware($accessToken);

        // ...........................................................

        $mockHandler = new MockHandler([
            new RequestException('Error Communicating with Server.', new Request('GET', 'On leave...')),
        ]);

        $historyContainer = [];
        $historyMiddleware = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);

        $handlerStack->push($oauthMiddleware);
        $handlerStack->push($historyMiddleware);

        $userChosenAccessToken = 'QBKNcn10frGUSlrbzE17ngD5W1f8L8dcMNPMZGD4V7NDj4CGws';

        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://localhost:8000/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$userChosenAccessToken,
            ],
        ]);

        $this->expectException(RequestException::class);

        $response = $client->request('GET', 'resource');
    }
}
