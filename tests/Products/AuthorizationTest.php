<?php

namespace Bmatovu\AirtelMoney\Tests\Products;

use Bmatovu\AirtelMoney\Products\Authorization;
use Bmatovu\AirtelMoney\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthorizationTest extends TestCase
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

        $authorization = new Authorization($mockClient);

        $apiRes = $authorization->getToken();

        $this->assertEquals($apiRes, $resBody);
    }
}
