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

            // Nombre de cartes tirees a chaque ouverture (CDC 5.1).
            $table->unsignedTinyInteger('cards_per_pack')->default(5);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table pivot : composition d'un pack. Le poids determine la
        // probabilite de tirage de la carte dans ce pack (CDC glossaire).
        Schema::create('pack_card', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('weight');
            $table->timestamps();

            // Une carte ne peut apparaitre qu'une fois dans un pack donne :
            // deux lignes pour la meme carte fausseraient le calcul des
            // probabilites affichees a l'administrateur.
            $table->unique(['pack_id', 'card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_card');
        Schema::dropIfExists('packs');
    }
};
