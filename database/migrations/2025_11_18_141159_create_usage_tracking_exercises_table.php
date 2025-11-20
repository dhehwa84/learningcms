<?php
// database/migrations/2024_01_16_000002_create_usage_tracking_exercises_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_tracking_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('device_id');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('exercise_id'); // The exercise identifier from content
            $table->string('exercise_type'); // multiple-choice, drag-match, etc.
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->json('answer_data')->nullable(); // Structured answer data
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('attempts')->default(1);
            $table->json('device_info');
            $table->string('api_key_id')->nullable();
            $table->timestamps();

            $table->index(['device_id']);
            $table->index(['exercise_id']);
            $table->index(['session_id']);
            $table->index(['project_id', 'section_id', 'unit_id']);
            $table->index(['completed_at']);
            $table->index(['api_key_id']);

            $table->foreign('session_id')->references('session_id')->on('usage_tracking_sessions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_tracking_exercises');
    }
};