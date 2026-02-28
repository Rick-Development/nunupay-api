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
        Schema::create('payscribe_virtual_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('card_id')->nullable();
            $table->double('amount', 11, 2)->nullable();
            $table->string('currency')->nullable();
            $table->string('brand')->nullable();
            $table->string('balance', 20)->nullable();
            $table->decimal('charge', 11, 2)->default(0.00);
            $table->string('trx_type', 10)->nullable();
            $table->string('remarks')->nullable();
            $table->string('trx_id')->nullable();
            $table->string('ref')->nullable();
            $table->string('action')->nullable();
            $table->string('event_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payscribe_virtual_card_transactions');
    }
};