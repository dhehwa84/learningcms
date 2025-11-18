<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->string('target_type'); // project, unit, section, etc.
            $table->string('target_id');
            $table->string('target_name');
            $table->enum('action_type', ['create', 'update', 'delete', 'export', 'view']);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['user_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};