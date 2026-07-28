<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('card_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('rarity_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('value');
            $table->unsignedSmallInteger('power');
            $table->unsignedSmallInteger('defense');

            $table->string('image_path')->nullable();

            $table->boolean('is_ai_generated')->default(false);
            $table->text('image_prompt')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['card_type_id', 'rarity_id']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
