<?php
// database/migrations/xxxx_xx_xx_000003_create_sections_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('project_id');
            $table->index(['project_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sections');
    }
};