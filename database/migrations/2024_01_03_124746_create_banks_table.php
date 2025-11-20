<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('country_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id');
            $table->string('name')->nullable();
            $table->string('bank_code')->nullable();
            $table->integer('operatorId')->nullable();
            $table->integer('localMinAmount')->nullable();
            $table->integer('localMaxAmount')->nullable();
            $table->integer('service_id');
            $table->text('services_form')->nullable();
            $table->boolean('status')->default(1)->comment('1=>Active, 0=>Inactive');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
