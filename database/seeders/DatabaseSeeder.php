<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
             $this->call(AdSeeder::class);
        // $this->call([
//            AdminSeeder::class,
//            FileStorageSeeder::class,
//            NotificationSeeder::class,
//            PageSeeder::class,
        // ]);
    }
}
