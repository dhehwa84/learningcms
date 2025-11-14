<?php
// database/migrations/xxxx_xx_xx_000008_create_content_blocks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accordion_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'richtext', 'image', 'video', 'audio', 'grid']);
            $table->integer('order')->default(0);
            $table->longText('content')->nullable();
            $table->string('layout', 50)->nullable();
            $table->timestamps();
            
            $table->index(['accordion_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_blocks');
    }
};