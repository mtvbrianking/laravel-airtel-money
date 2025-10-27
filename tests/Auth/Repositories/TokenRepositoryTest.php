<?php

namespace Bmatovu\AirtelMoney\Tests\Auth\Repositories;

use Bmatovu\AirtelMoney\Auth\Models\TokenInterface;
use Bmatovu\AirtelMoney\Auth\Repositories\TokenRepository;
use Bmatovu\AirtelMoney\Auth\Repositories\TokenRepositoryInterface;
use Bmatovu\AirtelMoney\Models\Token;
use Bmatovu\AirtelMoney\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;

class TokenRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_create_token(): void
    {
        $tokenAttrs = [
            'access_token' => Str::random(60),
            'refresh_token' => Str::random(60),
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        $tokenRepo = new TokenRepository;

        $this->assertInstanceOf(TokenRepositoryInterface::class, $tokenRepo);

        $token = $tokenRepo->create($tokenAttrs);

        $this->assertInstanceOf(TokenInterface::class, $token);

        $this->assertInstanceOf(Carbon::class, $token->getExpiresAt());
    }

    // public function test_doesnt_retrieve_expired_tokens() {}: void
    // public function test_doesnt_retrieve_deleted_tokens(): void
    // {
    //     Token::factory()->create();

    //     $tokenRepo = new TokenRepository;

    //     $tokens = $tokenRepo->retrieveAll();

    //     $this->assertInstanceOf(Collection::class, $tokens);

    //     $this->assertEquals(0, $tokens->count());
    // }

    public function test_can_retrieve_all_tokens(): void
    {
        Token::factory()->count(3)->create();

        $tokenRepo = new TokenRepository;

        $tokens = $tokenRepo->retrieveAll();

        $this->assertInstanceOf(Collection::class, $tokens);

        $this->assertEquals(3, $tokens->count());
    }

    public function test_can_retrieve_token_access_token(): void
    {
        Token::factory()->count(2)->create();

        $access_token = Str::random(60);

        Token::factory()->create([
            'access_token' => $access_token,
        ]);

        $tokenRepo = new TokenRepository;

        $token = $tokenRepo->retrieve($access_token);

        $this->assertNotInstanceOf(Collection::class, $token);

        $this->assertInstanceOf(Token::class, $token);

        $this->assertEquals($access_token, $token->getAccessToken());

        // Test retrieving non-existent token

        $access_token = Str::random(60);

        $token = $tokenRepo->retrieve($access_token);

        $this->assertNull($token);
    }

    public function test_can_retrieve_lastest_token(): void
    {
        $tokenRepo = new TokenRepository;

        $token = $tokenRepo->retrieve();

        $this->assertNull($token);

        // ...

        Token::factory()->count(2)->create();

        $access_token = Str::random(60);

        Token::factory()->create([
            'access_token' => $access_token,
            'created_at' => Carbon::now()->addSeconds(10),
        ]);

        $tokenRepo = new TokenRepository;

        $token = $tokenRepo->retrieve();

        $this->assertInstanceOf(Token::class, $token);

        $this->assertEquals($access_token, $token->getAccessToken());
    }

    public function test_can_update_token(): void
    {
        $org_access_token = Str::random(60);

        Token::factory()->create([
            'access_token' => $org_access_token,
        ]);

        // ...

        $tokenRepo = new TokenRepository;

        $new_access_token = Str::random(60);
        $new_refresh_token = Str::random(60);
        $new_token_type = 'New_Bearer';

        $new_token = $tokenRepo->update($org_access_token, [
            'access_token' => $new_access_token,
            'refresh_token' => $new_refresh_token,
            'token_type' => $new_token_type,
            // 'expires_at' => 3600, // now()->addSeconds(3600)->toDateTimeString(),
        ]);

        $this->assertEquals($new_access_token, $new_token->getAccessToken());
        $this->assertEquals($new_refresh_token, $new_token->getRefreshToken());
        $this->assertEquals($new_token_type, $new_token->getTokenType());
        // $this->assertInstanceOf(Carbon::class, $new_token->getExpiresAt());
        // $this->assertNotEquals($org_token->getExpiresAt()->format('Y-m-d H:i:s'), $new_token->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    public function test_can_delete_token(): void
    {
        $access_token = Str::random(60);

        $token = Token::factory()->create([
            'access_token' => $access_token,
        ]);

        $table = $token->getTable();

        $tokenRepo = new TokenRepository;

        $tokenRepo->delete($access_token);

        $this->assertDatabaseMissing($table, [
            'access_token' => $access_token,
        ]);
    }
}
