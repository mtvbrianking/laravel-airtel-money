<?php

namespace Bmatovu\AirtelMoney\Tests\Products;

use Bmatovu\AirtelMoney\Products\Collection;
use Bmatovu\AirtelMoney\Tests\TestCase;
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
}
