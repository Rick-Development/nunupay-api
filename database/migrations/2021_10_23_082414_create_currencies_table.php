<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('world.migrations.currencies.table_name'), function (Blueprint $table) {
			$table->id();
			$table->foreignId('country_id');
			$table->string('name')->nullable();
			$table->string('code')->nullable();
			$table->decimal('rate',18,8)->default(0);
			$table->tinyInteger('default')->default(0)->comment('0=>No, 1=>Yes');
			$table->tinyInteger('precision')->default(2);
			$table->string('symbol')->nullable();
			$table->string('symbol_native')->nullable();
			$table->tinyInteger('symbol_first')->default(1);
			$table->string('decimal_mark', 1)->default('.');
			$table->string('thousands_separator', 1)->default(',');
            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists(config('world.migrations.currencies.table_name'));
	}
}
