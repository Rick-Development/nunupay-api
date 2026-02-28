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
        Schema::create('virtual_card_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('image_driver')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('parameters')->nullable();
            $table->text('currencies')->nullable();
            $table->string('debit_currency', 20)->nullable();
            $table->text('extra_parameters')->nullable();
            $table->text('add_fund_parameter')->nullable();
            $table->text('form_field')->nullable();
            $table->text('currency')->nullable();
            $table->text('symbol')->nullable();
            $table->mediumText('info_box')->nullable();
            $table->text('alert_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_card_methods');
    }
};
