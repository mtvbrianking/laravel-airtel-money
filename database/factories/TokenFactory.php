<?php

namespace Bmatovu\AirtelMoney\Database\Factories;

use Bmatovu\AirtelMoney\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TokenFactory extends Factory
{
    protected $model = Token::class;

    public function definition(): array
    {
        return [
            'access_token' => Str::random(60),
            'refresh_token' => Str::random(60),
            'token_type' => $this->faker->randomElement(['Basic', 'Bearer']),
            'expires_at' => $this->faker->dateTime('now', null),
        ];
    }
}
