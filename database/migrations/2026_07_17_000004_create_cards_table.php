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

            // restrictOnDelete : supprimer un type ou une rarete encore utilise
            // laisserait des cartes orphelines. L'admin doit d'abord reaffecter
            // les cartes concernees.
            $table->foreignId('card_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('rarity_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('value');
            $table->unsignedSmallInteger('power');
            $table->unsignedSmallInteger('defense');

            $table->string('image_path')->nullable();

            // Tracabilite de la generation IA (CDC 9).
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
