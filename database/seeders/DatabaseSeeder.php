<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        // ensure admin account exists and has known credentials on every seed
        // Use updateOrCreate so running migrate:fresh --seed will always set these values
        User::updateOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'admin',
            // The User model casts password => 'hashed', so saving plain 'password' will be hashed.
            'password' => 'password',
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
        ]);


        // call other seeders and delivery seeder
        $this->call([
            PostSeeder::class,
            ContactSeeder::class,
            DeliverySeeder::class,
        ]);
    }
}
