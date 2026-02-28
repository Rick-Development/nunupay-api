<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->nullable();
            $table->string('blog_image')->nullable();
            $table->string('blog_image_driver')->nullable();
            $table->string('author_image')->nullable();
            $table->string('author_image_driver')->nullable();
            $table->boolean('breadcrumb_status')->default(1)->comment('1=>on, 0=>off');
            $table->string('breadcrumb_image')->nullable();
            $table->string('breadcrumb_image_driver')->nullable();
            $table->boolean('status')->default(1)->comment('1=>Active, 0=>Inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
