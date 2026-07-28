<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();

            $table->foreignId('user_card_id')->constrained()->restrictOnDelete();

            $table->enum('side', ['offered', 'requested']);
            $table->timestamps();

            $table->unique(['trade_id', 'user_card_id']);
            $table->index(['trade_id', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_items');
    }
};
