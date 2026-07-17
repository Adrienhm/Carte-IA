<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cartes engagees dans un echange.
        //
        // 'offered'   : exemplaires appartenant a l'initiateur.
        // 'requested' : exemplaires appartenant au destinataire.
        Schema::create('trade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete : on ne peut pas supprimer un exemplaire engage
            // dans un echange. L'echange doit d'abord etre resolu ou annule,
            // sinon une carte pourrait disparaitre en cours de negociation.
            $table->foreignId('user_card_id')->constrained()->restrictOnDelete();

            $table->enum('side', ['offered', 'requested']);
            $table->timestamps();

            // Un exemplaire ne peut etre liste qu'une fois par echange.
            $table->unique(['trade_id', 'user_card_id']);
            $table->index(['trade_id', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_items');
    }
};
