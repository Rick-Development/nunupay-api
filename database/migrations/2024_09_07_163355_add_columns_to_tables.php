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
		Schema::table('transactions', function (Blueprint $table) {
            $table->text('note')->nullable();
        });
			
		Schema::table('pages', function (Blueprint $table) {
           $table->text('og_description')->nullable();
           $table->text('meta_robots')->nullable();
		});
		
        Schema::table('virtual_card_transactions', function (Blueprint $table) {
            $table->string('trx_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('note');
        });
		
		Schema::table('pages', function (Blueprint $table) {
           $table->dropColumn('og_description');
           $table->dropColumn('meta_robots');
		});
		
		Schema::table('virtual_card_transactions', function (Blueprint $table) {
            $table->dropColumn('trx_id');
        });
    }
};
