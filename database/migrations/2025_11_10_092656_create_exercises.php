<?php
// database/migrations/xxxx_xx_xx_000010_create_exercises_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accordion_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['multiple-choice', 'radio', 'checkbox', 'text', 'number', 'mixed', 'drag-match']);
            $table->string('title', 500)->nullable();
            $table->integer('order')->default(0);
            $table->enum('question_numbering', ['123', 'abc'])->default('123');
            $table->json('labels')->nullable();
            $table->timestamps();
            
            $table->index(['accordion_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exercises');
    }
};