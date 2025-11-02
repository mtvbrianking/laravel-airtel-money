<?php

namespace Bmatovu\AirtelMoney\Tests\Products;

use Bmatovu\AirtelMoney\Products\Account;
use Bmatovu\AirtelMoney\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AccountTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_get_balance(): void
    {
        $resBody = [
            'data' => [
                'balance' => '230486.65',
                'currency' => 'UGX',
                'account_status' => 'Active',
            ],
            'status' => [
                'response_code' => 'DP02100000001',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'SUCCESS',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $account = new Account($mockClient);

        $apiRes = $account->getBalance();

        $this->assertEquals($apiRes, $resBody);
    }
}
