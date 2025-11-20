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
        Schema::create('payscribe_virtual_card_deatils', function (Blueprint $table) {
            $table->id();
            $table->string('trans_id');
            $table->foreignId('user_id')->constrained('users');
            $table->string('ref')->nullable();
            $table->string('card_id');
            $table->string('card_name');
            $table->string('card_number');
            $table->string('card_type');
            $table->string('currency');
            $table->string('brand');
            $table->string('masked');
            $table->string('expiry_date');
            $table->string('cvv');
            $table->string('cvv');
            $table->string('ref');
            $table->json('billing_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payscribe_virtual_card_deatils');
    }
};