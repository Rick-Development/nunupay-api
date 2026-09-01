<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove unused third-party automatic payment gateways
        if (Schema::hasTable('gateways')) {
            DB::table('gateways')->where('is_automatic', 1)->delete();
        }
    }

    public function down(): void
    {
        // No rollbacks needed for third-party gateways cleanup
    }
};
