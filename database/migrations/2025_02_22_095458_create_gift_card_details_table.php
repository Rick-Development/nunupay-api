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
        Schema::create('gift_card_details', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('giftcard_code');
            $table->string('country_code');
            $table->string('currency_code');
            $table->string('giftcard_brand');
            $table->json('images');
            $table->string('redeem_instructions');
            $table->string('sale_price');
            $table->string('seller_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_card_details');
    }
};