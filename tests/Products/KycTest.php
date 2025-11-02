<?php

namespace Bmatovu\AirtelMoney\Tests\Products;

use Bmatovu\AirtelMoney\Products\Kyc;
use Bmatovu\AirtelMoney\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class KycTest extends TestCase
{
    use DatabaseMigrations;

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

        $Kyc = new Kyc($mockClient);

        $user = $Kyc->getUser('700123123');

        $this->assertEquals($user, $resBody);
    }
}
