<?php

namespace Database\Seeders;

use App\Models\User;
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

        // ensure admin account exists (avoid duplicate key on repeated seeds)
        User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'admin',
        ]);


        // call other seeders and delivery seeder
        $this->call([
            PostSeeder::class,
            ContactSeeder::class,
            DeliverySeeder::class,
        ]);
    }
}
