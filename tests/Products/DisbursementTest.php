<?php

namespace Bmatovu\AirtelMoney\Tests\Products;

use Bmatovu\AirtelMoney\Products\Disbursement;
use Bmatovu\AirtelMoney\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DisbursementTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_disburse(): void
    {
        $resBody = [
            'data' => [
                'transaction' => [
                    'reference_id' => '133412377178',
                    'airtel_money_id' => 'disburs-6EJL6CWF1D-ba1451ae-d96a-4393-b46c-b2a26cff71a3',
                    'id' => 'ba1451ae-d96a-4393-b46c-b2a26cff71a3',
                    'status' => 'TS',
                ],
            ],
            'status' => [
                'response_code' => 'DP00900001001',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'You have deposited UGX 500 on 23-October-2025 20:59 Mobile Number: 0700123123 Trans ID: 133412387177. Your bal: UGX 65,252.',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $disbursement = new Disbursement($mockClient);

        $apiRes = $disbursement->send('700123123', 500);

        $this->assertEquals($apiRes, $resBody);
    }

    public function test_can_get_transaction(): void
    {
        $resBody = [
            'data' => [
                'transaction' => [
                    'airtel_money_id' => 'disburs-6EJL6CWF1D-ba1451ae-d96a-4393-b46c-b2a26cff71a3',
                    'id' => 'ba1451ae-d96a-4393-b46c-b2a26cff71a3',
                    'message' => 'You have deposited UGX 500 on 23-October-2025 20:59 Mobile Number: 0700123123 Trans ID: 133412387177. Your bal: UGX 65,252.',
                    'status' => 'TS',
                ],
            ],
            'status' => [
                'response_code' => 'DP00900001001',
                'code' => '200',
                'success' => true,
                'result_code' => 'ESB000010',
                'message' => 'SUCCESS',
            ],
        ];

        $response = new Response(200, [], json_encode($resBody));

        $mockClient = $this->mockGuzzleClient($response);

        $disbursement = new Disbursement($mockClient);

        $apiRes = $disbursement->getTransaction('ba1451ae-d96a-4393-b46c-b2a26cff71a3');

        $this->assertEquals($apiRes, $resBody);
    }
}
