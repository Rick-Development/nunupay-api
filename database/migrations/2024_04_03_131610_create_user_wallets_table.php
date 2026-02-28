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
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('currency_code')->nullable();
            $table->decimal('balance', 16, 8)->default(0);
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->tinyInteger('default')->default(0)->comment('1 = Yes, 0 = No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};
