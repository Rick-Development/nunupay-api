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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('language_id')->unsigned();
            $table->foreign('language_id')->references('id')->on('languages');
            $table->string('name', 255)->nullable();
            $table->string('email_from', 191)->nullable();
            $table->string('template_key', 255)->nullable();
            $table->text('subject')->nullable();
            $table->text('short_keys')->nullable();
            $table->text('email')->nullable();
            $table->text('sms')->nullable();
            $table->text('in_app')->nullable();
            $table->text('push')->nullable();
            $table->string('status', 191)->nullable()->comment('mail = 0(inactive), mail = 1(active), sms = 0(inactive), sms = 1(active), in_app = 0(inactive), in_app = 1(active), push = 0(inactive), push = 1(active),');
            $table->tinyInteger('notify_for')->default(0)->comment('0 => user, 1 => admin');
            $table->string('lang_code', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
