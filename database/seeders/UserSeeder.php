<?php

namespace Database\Seeders;

use App\Models\Pack;
use App\Models\User;
use App\Models\UserPack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Comptes de demonstration : un administrateur et deux joueurs, chacun dote de
 * quelques packs pour pouvoir tester immediatement ouverture et echange.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@nationsglory.test'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $alice = User::updateOrCreate(
            ['email' => 'alice@nationsglory.test'],
            [
                'name' => 'Alice',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $bob = User::updateOrCreate(
            ['email' => 'bob@nationsglory.test'],
            [
                'name' => 'Bob',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $packs = Pack::all();

        // Dote chaque joueur de 5 packs pour la demo.
        foreach ([$admin, $alice, $bob] as $user) {
            foreach ($packs as $pack) {
                for ($i = 0; $i < 3; $i++) {
                    UserPack::create([
                        'user_id' => $user->id,
                        'pack_id' => $pack->id,
                        'source' => 'seed',
                    ]);
                }
            }
        }
    }
}
