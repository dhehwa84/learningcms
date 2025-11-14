<?php
// database/migrations/xxxx_xx_xx_000005_create_unit_header_media_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unit_header_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['audio', 'video', 'image']);
            $table->string('url', 500);
            $table->string('alt')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();
            
            $table->unique('unit_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('unit_header_media');
    }
};