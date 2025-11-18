<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->constrained('project_statuses');
            $table->foreignId('sign_off_person_id')->nullable()->constrained('users');
            $table->dateTime('expected_start_date')->nullable();
            $table->dateTime('completion_date')->nullable();
            
            // Add indexes for better performance
            $table->index('status_id');
            $table->index('sign_off_person_id');
            $table->index('expected_start_date');
            $table->index('completion_date');
        });

        // Set default status for existing projects
        DB::table('projects')->update([
            'status_id' => DB::table('project_statuses')->where('name', 'Planning')->value('id')
        ]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropForeign(['sign_off_person_id']);
            $table->dropColumn(['status_id', 'sign_off_person_id', 'expected_start_date', 'completion_date']);
        });
    }
};