<?php
// database/migrations/xxxx_xx_xx_000009_create_content_block_media_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_block_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_block_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video', 'audio']);
            $table->string('url', 500);
            $table->string('alt')->nullable();
            $table->text('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_block_media');
    }
};