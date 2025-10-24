<?php

namespace Bmatovu\AirtelMoney\Models;

use Bmatovu\AirtelMoney\Database\Factories\TokenFactory;
use Bmatovu\AirtelMoney\Traits\TokenUtils;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Token extends BaseModel // implements TokenInterface
{
    /**
     * @use HasFactory<\Bmatovu\AirtelMoney\Database\Factories\TokenFactory>
     */
    use HasFactory, TokenUtils;

    /**
     * @var string|null
     */
    protected $table = 'airtel_money_tokens';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * @return \Bmatovu\AirtelMoney\Database\Factories\TokenFactory
     */
    public static function newFactory(): Factory
    {
        return TokenFactory::new();
    }
}
