<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('overview')->nullable(); // can hold objectives JSON
            $table->string('level')->nullable();
            $table->string('theme')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media_library')->nullOnDelete();
            $table->foreignId('audio_media_id')->nullable()->constrained('media_library')->nullOnDelete();
            $table->foreignId('video_media_id')->nullable()->constrained('media_library')->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['section_id', 'number']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('units');
    }
};
