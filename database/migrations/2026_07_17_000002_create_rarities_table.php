<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rarities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#9ca3af');

            // Poids par defaut propose lorsqu'une carte de cette rarete est
            // ajoutee a un pack. Le tirage reel utilise pack_card.weight :
            // ceci n'est qu'une valeur de confort pour l'administrateur.
            $table->unsignedInteger('default_weight');

            // Grille de valeurs (CDC 7.2) : valeur de reference d'une carte de
            // cette rarete, utilisee comme valeur par defaut a la creation.
            $table->unsignedInteger('base_value');

            // Bornes de statistiques utilisees par le generateur pour garder des
            // stats coherentes avec la rarete (CDC 9.1).
            $table->unsignedSmallInteger('min_stat');
            $table->unsignedSmallInteger('max_stat');

            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rarities');
    }
};
