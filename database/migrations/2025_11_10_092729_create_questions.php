<?php
// database/migrations/xxxx_xx_xx_000011_create_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('type', ['dropdown', 'radio', 'checkbox', 'text', 'number', 'multiple-choice']);
            $table->integer('order')->default(0);
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->json('correct_answers')->nullable();
            $table->timestamps();
            
            $table->index(['exercise_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};