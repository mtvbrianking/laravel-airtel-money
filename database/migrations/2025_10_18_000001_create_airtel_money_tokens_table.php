<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('airtel_money_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->string('token_type')->default('Bearer');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
        });
    }

    public function down()
    {
        Schema::drop('airtel_money_tokens');
    }
};
