<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_unit_intro_media_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('unit_intro_media', function (Blueprint $t) {
      $t->id();
      $t->foreignId('unit_id')->constrained()->cascadeOnDelete();
      $t->foreignId('media_id')->constrained('media_library')->cascadeOnDelete();
      $t->unsignedInteger('sort_order')->default(0);
      $t->string('caption')->nullable(); // optional, any language
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('unit_intro_media');
  }
};

