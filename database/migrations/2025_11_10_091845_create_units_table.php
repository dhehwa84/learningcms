<?php
// database/migrations/xxxx_xx_xx_000004_create_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->integer('number');
            $table->string('title');
            $table->string('grade')->nullable();
            $table->string('theme')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('section_id');
            $table->index(['section_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('units');
    }
};