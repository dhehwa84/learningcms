<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accordion_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // rich_text, image, audio, video, html, embed, exercise
            $table->json('payload')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blocks');
    }
};
