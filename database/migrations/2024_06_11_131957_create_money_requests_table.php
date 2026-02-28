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
        Schema::create('money_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('requester_id');
            $table->bigInteger('recipient_id');
            $table->string('wallet_uuid');
            $table->decimal('amount', 15, 2);
            $table->string('currency');
            $table->tinyInteger('status')->default(0)->comment('0=>pending,1=success,2=>rejected');
            $table->string('trx_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_requests');
    }
};
