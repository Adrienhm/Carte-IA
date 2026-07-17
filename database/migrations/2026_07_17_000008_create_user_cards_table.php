<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventaire : une ligne = un exemplaire possede.
        //
        // On ne stocke pas une quantite mais bien un exemplaire par ligne :
        // c'est ce qui permet de bloquer individuellement une carte engagee
        // dans un echange (CDC 5.1 "Blocage des cartes") sans immobiliser les
        // doublons que le joueur possede par ailleurs.
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete : supprimer une carte du catalogue ne doit pas
            // faire disparaitre silencieusement les exemplaires des joueurs.
            $table->foreignId('card_id')->constrained()->restrictOnDelete();

            // Provenance de l'exemplaire : 'pack', 'trade' ou 'admin'.
            $table->string('source', 32)->default('pack');

            $table->foreignId('pack_opening_id')->nullable()
                ->constrained()->nullOnDelete();

            // Echange qui immobilise actuellement cet exemplaire.
            //
            // Une seule colonne (et non une table de liaison) : un exemplaire
            // ne peut etre engage que dans un echange a la fois, ce qui rend le
            // blocage exclusif par construction. Impossible de proposer deux
            // fois la meme carte a deux joueurs differents.
            $table->foreignId('locked_by_trade_id')->nullable()
                ->constrained('trades')->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'card_id']);
            $table->index('locked_by_trade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
