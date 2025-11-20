<?php

namespace Database\Seeders;

use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['name' => 'blog', 'slug' => 'blog', 'template_name' => 'light', 'type' => 1],
            ['name' => 'login', 'slug' => 'login', 'template_name' => 'light', 'type' => 2],
            ['name' => 'register', 'slug' => 'register', 'template_name' => 'light', 'type' => 2],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['name' => $page['name']],
                [
                    'slug' => $page['slug'],
                    'template_name' => $page['template_name'],
                    'type' => $page['type'],
                ],
                [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

    }
}
