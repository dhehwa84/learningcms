<?php
// database/migrations/2024_01_16_000001_create_usage_tracking_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_tracking_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('device_id'); // Anonymous device identifier
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('device_info'); // Store all device characteristics
            $table->string('api_key_id')->nullable(); // Which API key was used
            $table->timestamps();

            $table->index(['device_id']);
            $table->index(['project_id']);
            $table->index(['started_at']);
            $table->index(['api_key_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_tracking_sessions');
    }
};