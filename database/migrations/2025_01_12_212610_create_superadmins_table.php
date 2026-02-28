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
        Schema::create('superadmins', function (Blueprint $table) {
             $table->id();
            $table->string('name', 100)->nullable();
            $table->string('username', 50)->nullable()->unique();
            $table->string('email', 191)->nullable()->unique();
            $table->string('password', 191)->nullable();
            $table->tinyInteger('two_fa')->default(0);
            $table->tinyInteger('two_fa_verify')->default(1);
            $table->string('two_fa_code')->nullable();
            $table->string('image', 191)->nullable();
            $table->string('image_driver', 50)->nullable();
            $table->string('phone', 191)->nullable();
            $table->text('address')->nullable();
            $table->text('admin_access')->nullable();
            $table->string('last_login', 50)->nullable();
            $table->dateTime('last_seen')->nullable();
            $table->tinyInteger('status')->default('0');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superadmins');
    }
};
