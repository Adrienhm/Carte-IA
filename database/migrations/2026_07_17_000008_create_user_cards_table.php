<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('card_id')->constrained()->restrictOnDelete();

            $table->string('source', 32)->default('pack');

            $table->foreignId('pack_opening_id')->nullable()
                ->constrained()->nullOnDelete();

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
