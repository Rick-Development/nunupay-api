<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->nullable();
            $table->integer('referral_id')->nullable();
            $table->boolean('refer_bonus')->default(0)->comment('1=>bonus_awarded, 0=>bonus_pending');
            $table->integer('language_id')->nullable();
            $table->string('email')->unique();
            $table->string('country_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('image')->nullable();
            $table->string('image_driver',50)->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('address_one')->nullable();
            $table->text('address_two')->nullable();
            $table->string('provider',191)->nullable();
            $table->integer('provider_id')->nullable();
            $table->boolean('status')->nullable();
            $table->boolean('two_fa')->default(0);
            $table->boolean('two_fa_verify')->default(1);
            $table->string('two_fa_code')->nullable();
            $table->boolean('email_verification')->nullable();
            $table->boolean('sms_verification')->nullable();
            $table->string('verify_code')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->dateTime('last_seen')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
