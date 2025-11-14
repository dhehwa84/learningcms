<?php
// database/migrations/xxxx_xx_xx_000015_create_labels_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key', 100);
            $table->text('value');
            $table->timestamps();
            
            $table->unique(['user_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('labels');
    }
};