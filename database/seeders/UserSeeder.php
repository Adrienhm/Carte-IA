<?php

namespace Database\Seeders;

use App\Models\Pack;
use App\Models\User;
use App\Models\UserPack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@nationsglory.test'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $admin->forceFill(['is_admin' => true])->save();

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
