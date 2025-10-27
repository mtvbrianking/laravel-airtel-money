<?php

namespace Bmatovu\AirtelMoney\Tests\Models;

use Bmatovu\AirtelMoney\Auth\Models\TokenInterface;
use Bmatovu\AirtelMoney\Models\Token;
use Bmatovu\AirtelMoney\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;

class TokenTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_create_token(): void
    {
        $data = [
            'access_token' => Str::random(60),
            'refresh_token' => Str::random(60),
            'token_type' => 'Bearer',
            'expires_at' => Carbon::now(),
        ];

        $token = Token::create($data);

        $this->assertInstanceOf(TokenInterface::class, $token);

        $this->assertEquals('airtel_money_tokens', $token->getTable());

        $this->assertDatabaseHas('airtel_money_tokens', $data);
    }

    public function test_getters(): void
    {
        $access_token = Str::random(60);
        $refresh_token = Str::random(60);
        $token_type = 'Bearer';
        $expires_at = Carbon::now();

        $token = Token::create([
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => $token_type,
            'expires_at' => $expires_at,
        ]);

        $this->assertEquals($access_token, $token->getAccessToken());
        $this->assertEquals($refresh_token, $token->getRefreshToken());
        $this->assertEquals($token_type, $token->getTokenType());
        $this->assertInstanceOf(Carbon::class, $token->getExpiresAt());
        $this->assertEquals($expires_at->format('Y-m-d H:i:s'), $token->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    public function test_determines_expired(): void
    {
        $token = Token::factory()->create([
            'expires_at' => null,
        ]);

        $this->assertFalse($token->isExpired());

        $token->expires_at = Carbon::now()->addSeconds(3600);
        $token->fresh();

        $this->assertFalse($token->isExpired());

        $token->expires_at = Carbon::now()->subSeconds(4800);
        $token->fresh();

        $this->assertTrue($token->isExpired());
    }
}
