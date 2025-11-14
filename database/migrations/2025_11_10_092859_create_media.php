<?php
// database/migrations/xxxx_xx_xx_000013_create_media_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->enum('type', ['image', 'audio', 'video']);
            $table->string('url', 500);
            $table->string('thumbnail', 500)->nullable();
            $table->string('alt')->nullable();
            $table->integer('size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
};