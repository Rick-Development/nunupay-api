<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('money_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->nullable();
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('recipient_id')->constrained('recipients');
            $table->bigInteger('send_currency_id')->nullable()->comment('Country ID');
            $table->bigInteger('receive_currency_id')->nullable()->comment('Country ID');
            $table->bigInteger('service_id')->nullable()->comment('Service Id');
            $table->decimal('send_amount',18,8)->nullable();
            $table->decimal('fees',18,8)->nullable();
            $table->decimal('payable_amount',18,8)->nullable();
            $table->string('sender_currency',10)->nullable()->comment('Currency Code');
            $table->decimal('rate',18,8)->nullable();
            $table->decimal('recipient_get_amount',18,8)->nullable();
            $table->string('receiver_currency',10)->nullable()->comment('Currency Code');
            $table->tinyInteger('status')->default(0)->comment('0=> Draft/Initiate, 1=> Completed, 2=> Awaiting, 3=> Rejected');
            $table->tinyInteger('payment_status')->default(0)->comment('0=> Pending, 1=> Completed, 3=> Rejected');
            $table->boolean('resubmitted')->default(1)->comment('0=> No, 1=> Yes');
            $table->text('reason')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->text('user_information')->nullable();
            $table->string('trx_id')->unique()->nullable();
            $table->integer('wallet_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_transfers');
    }
};
