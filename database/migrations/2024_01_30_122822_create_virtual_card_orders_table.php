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
        Schema::create('virtual_card_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_card_method_id')->constrained('virtual_card_methods');
            $table->foreignId('user_id')->constrained('users');
            $table->string('currency');
            $table->text('form_input');
            $table->tinyInteger('status')->default(0)->comment('0=>pending,1=>approve,2=>rejected,3=>resubmit,4=>generate,5=>block rqst,6=>fund rejected,7=>block,8=>add_fund_rqst,9=>inactive');
            $table->double('fund_amount')->default(0);
            $table->double('fund_charge')->default(0);
            $table->text('reason')->nullable();
            $table->tinyInteger('resubmitted')->default(0)->comment('0=>no,1=>yes');
            $table->double('charge')->default(0)->comment('admin charge');
            $table->string('charge_currency')->nullable()->comment('admin base currency');
            $table->text('card_info')->nullable()->comment('response card information');
            $table->double('balance')->default(0);
            $table->string('cvv')->nullable();
            $table->text('card_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('brand')->nullable();
            $table->string('name_on_card')->nullable();
            $table->string('card_Id')->nullable();
            $table->text('last_error')->nullable()->comment('api given last error');
            $table->text('test')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_card_orders');
    }
};
