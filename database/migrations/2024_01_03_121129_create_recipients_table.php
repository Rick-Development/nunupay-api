<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('recipients', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->nullable();
            $table->foreignId('user_id');
            $table->integer('type')->default(1)->comment('0=>Myself, 1=>Others');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->foreignId('service_id')->nullable();
            $table->foreignId('bank_id')->nullable();
            $table->text('bank_info')->nullable();
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('recipients');
    }
};
