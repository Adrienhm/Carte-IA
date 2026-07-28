<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->unsignedTinyInteger('cards_per_pack')->default(5);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pack_card', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('weight');
            $table->timestamps();

            $table->unique(['pack_id', 'card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_card');
        Schema::dropIfExists('packs');
    }
};
