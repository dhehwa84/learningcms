<?php
// database/migrations/2024_01_16_000005_create_tracking_api_keys_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tracking_api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // The static API key
            $table->string('name'); // Human-readable name (e.g., "School XYZ")
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('allowed_projects')->nullable(); // Specific projects this key can access
            $table->integer('rate_limit')->default(100); // Requests per minute
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index(['key']);
            $table->index(['is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_api_keys');
    }
};