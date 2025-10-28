<?php

namespace Bmatovu\AirtelMoney\Tests;

use Bmatovu\AirtelMoney\Collection;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CollectionTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_collect(): void
    {
        $resBody = [
            'data' => [
                'transaction' => [
                    'id' => 'd56afb6b-24ff-4371-9dc0-1c64a051cbfc',
                    'status' => 'Success.',
                ],
            ],
            'status' => [
                'response_code' => 'DP00800001006',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'Success.',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $collection = new Collection($mockClient);

        $apiRes = $collection->receive('700123123', 500);

        $this->assertEquals($apiRes, $resBody);
    }

    public function test_can_get_transaction(): void
    {
        $resBody = [
            'data' => [
                'transaction' => [
                    'airtel_money_id' => '133410367531',
                    'id' => 'd56afb6b-24ff-4371-9dc0-1c64a051cbfc',
                    'message' => 'Your transaction has been successfully processed',
                    'status' => 'TS',
                ],
            ],
            'status' => [
                'response_code' => 'DP00800001001',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'SUCCESS',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $collection = new Collection($mockClient);

        $apiRes = $collection->getTransaction('d56afb6b-24ff-4371-9dc0-1c64a051cbfc');

        $this->assertEquals($apiRes, $resBody);
    }

    public function test_can_refund(): void
    {
        $resBody = [
            'data' => [
                'transaction' => [
                    'airtel_money_id' => '133410367531',
                    'status' => 'Success.',
                ],
            ],
            'status' => [
                'response_code' => 'DP00800001006',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'Success.',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $collection = new Collection($mockClient);

        $apiRes = $collection->refund('133410367531');

        $this->assertEquals($apiRes, $resBody);
    }

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

        $collection = new Collection($mockClient);

        $apiRes = $collection->getBalance();

        $this->assertEquals($apiRes, $resBody);
    }

    public function test_can_get_user(): void
    {
        $resBody = [
            'data' => [
                'is_barred' => false,
                'grade' => 'MCOM',
                'last_name' => 'JOHN',
                'registration' => [
                    'status' => 'MCOM',
                ],
                'msisdn' => '700123123',
                'first_name' => 'DOE',
                'is_pin_set' => true,
            ],
            'status' => [
                'response_code' => 'DP02200000001',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'SUCCESS',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $collection = new Collection($mockClient);

        $apiRes = $collection->getUser('700123123');

        $this->assertEquals($apiRes, $resBody);
    }
}
