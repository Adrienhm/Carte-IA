<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Types de cartes dynamiques : l'administrateur peut en ajouter,
        // modifier ou supprimer (CDC 5.1 "Types dynamiques").
        Schema::create('card_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();

            // Fragment injecte dans le prompt d'image envoye a l'IA (CDC 9.2).
            $table->string('prompt_hint')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
