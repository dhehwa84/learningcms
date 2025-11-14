<?php
// database/migrations/xxxx_xx_xx_000006_create_objectives_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->text('text');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['unit_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('objectives');
    }
};