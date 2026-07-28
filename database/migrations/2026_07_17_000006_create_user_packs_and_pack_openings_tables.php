<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('pack_id')->constrained()->restrictOnDelete();

            $table->string('source', 32)->default('admin');

            $table->timestamps();

            $table->index(['user_id', 'pack_id']);
        });

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
