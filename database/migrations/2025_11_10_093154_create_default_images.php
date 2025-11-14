<?php
// database/migrations/xxxx_xx_xx_000014_create_default_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('default_images', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('url', 500);
            $table->string('alt')->nullable();
            $table->enum('category', ['icon', 'illustration', 'photo', 'decoration']);
            $table->timestamps();
            
            $table->index('category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('default_images');
    }
};