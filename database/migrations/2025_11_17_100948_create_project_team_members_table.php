<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['lead', 'member', 'viewer'])->default('member');
            $table->timestamps();
            
            $table->unique(['project_id', 'user_id']);
        });

        // Add index for better performance
        Schema::table('project_team_members', function (Blueprint $table) {
            $table->index(['user_id', 'role']);
            $table->index(['project_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team_members');
    }
};