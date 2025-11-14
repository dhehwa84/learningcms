<?php
// database/migrations/xxxx_xx_xx_000007_create_accordions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accordions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('icon_url', 500)->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['unit_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accordions');
    }
};