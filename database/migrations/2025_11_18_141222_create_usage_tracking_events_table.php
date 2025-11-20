<?php
// database/migrations/2024_01_16_000003_create_usage_tracking_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('device_id');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('event_type'); // project_access, section_access, unit_access, exercise_view, etc.
            $table->string('target_id')->nullable(); // section_id, unit_id, etc.
            $table->string('target_name')->nullable();
            $table->timestamp('timestamp');
            $table->json('event_data')->nullable(); // Additional event-specific data
            $table->json('device_info');
            $table->string('api_key_id')->nullable();
            $table->timestamps();

            $table->index(['device_id']);
            $table->index(['session_id']);
            $table->index(['event_type']);
            $table->index(['project_id', 'section_id', 'unit_id']);
            $table->index(['timestamp']);
            $table->index(['api_key_id']);

            $table->foreign('session_id')->references('session_id')->on('usage_tracking_sessions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_tracking_events');
    }
};