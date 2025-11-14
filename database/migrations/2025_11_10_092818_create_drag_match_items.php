<?php
// database/migrations/xxxx_xx_xx_000012_create_drag_match_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('drag_match_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->enum('left_type', ['text', 'image']);
            $table->text('left_value');
            $table->string('left_alt')->nullable();
            $table->enum('right_type', ['text', 'image']);
            $table->text('right_value');
            $table->string('right_alt')->nullable();
            $table->timestamps();
            
            $table->index(['exercise_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('drag_match_items');
    }
};