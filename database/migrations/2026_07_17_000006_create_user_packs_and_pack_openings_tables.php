<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Packs non ouverts possedes par un joueur.
        //
        // Choix d'equilibrage (CDC 7.3) : un joueur ne peut pas ouvrir un pack
        // a volonte, il doit en posseder un. Sans cette contrainte la quantite
        // de packs distribues serait infinie et les raretes n'auraient plus
        // aucune valeur -- exactement l'ecueil que le CDC demande d'eviter.
        Schema::create('user_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete : on refuse de supprimer un pack encore detenu
            // par des joueurs, cela reviendrait a leur retirer leur bien.
            $table->foreignId('pack_id')->constrained()->restrictOnDelete();

            // Comment le joueur a obtenu ce pack : inscription, don admin, etc.
            $table->string('source', 32)->default('admin');

            $table->timestamps();

            $table->index(['user_id', 'pack_id']);
        });

        // Historique des ouvertures : trace chaque tirage effectue par le
        // serveur, utile pour verifier l'equilibrage reel a posteriori.
        Schema::create('pack_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pack_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_openings');
        Schema::dropIfExists('user_packs');
    }
};
