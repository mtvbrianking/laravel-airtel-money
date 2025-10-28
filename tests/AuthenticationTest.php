<?php

namespace Bmatovu\AirtelMoney\Tests;

use Bmatovu\AirtelMoney\Authentication;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_get_token(): void
    {
        $resBody = [
            'access_token' => 'pQWBjnwY0gS04euamZVVl4zDWfE4Pqkp',
            'token_type' => 'bearer',
            'expires_in' => 180,
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $authentication = new Authentication($mockClient);

        $apiRes = $authentication->getToken();

        $this->assertEquals($apiRes, $resBody);
    }
}
