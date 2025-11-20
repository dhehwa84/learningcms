<?php
// database/migrations/2024_01_16_000004_create_usage_tracking_feedback_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_tracking_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('device_id');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('user_type', ['student', 'teacher', 'parent', 'other'])->default('student');
            $table->enum('feedback_type', ['improvement', 'bug', 'feature_request', 'general']);
            $table->integer('rating')->nullable(); // 1-5 scale
            $table->text('message');
            $table->string('contact_email')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'addressed', 'archived'])->default('pending');
            $table->json('device_info');
            $table->string('api_key_id')->nullable();
            $table->timestamps();

            $table->index(['device_id']);
            $table->index(['session_id']);
            $table->index(['feedback_type']);
            $table->index(['status']);
            $table->index(['rating']);
            $table->index(['api_key_id']);

            $table->foreign('session_id')->references('session_id')->on('usage_tracking_sessions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_tracking_feedback');
    }
};