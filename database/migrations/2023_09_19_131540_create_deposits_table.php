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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->integer('depositable_id')->nullable();
            $table->string('depositable_type')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('payment_method_id')->unsigned()->nullable();
            $table->string('payment_method_currency')->nullable();
            $table->decimal('amount', 18, 8)->default(0.00000000);
            $table->integer('wallet_id')->nullable();
            $table->decimal('percentage_charge', 18, 8)->default(0.00000000);
            $table->decimal('fixed_charge', 18, 8)->default(0.00000000);
            $table->decimal('payable_amount', 18, 8)->default(0.00000000)->comment('Amount paid');
            $table->double('base_currency_charge', 18, 8)->default(0.00000000);
            $table->double('payable_amount_in_base_currency', 18, 8)->default(0.00000000);
            $table->decimal('btc_amount', 18, 8)->nullable();
            $table->string('btc_wallet')->nullable();
            $table->string('payment_id', 191)->nullable();
            $table->text('information')->nullable();
            $table->char('trx_id', 36)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=success, 2=request, 3=rejected');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
