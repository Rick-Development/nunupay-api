<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@bugfinder.net',
            'password' => Hash::make('admin'),
            'status' => 1,
        ]);
    }
}
