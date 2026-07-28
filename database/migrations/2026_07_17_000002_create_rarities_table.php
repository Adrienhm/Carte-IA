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

            $table->unsignedInteger('default_weight');

            $table->unsignedInteger('base_value');

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
