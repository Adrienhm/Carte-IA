<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RaritySeeder::class,
            CardTypeSeeder::class,
            CardSeeder::class,
            PackSeeder::class,
            UserSeeder::class,
        ]);
    }
}
