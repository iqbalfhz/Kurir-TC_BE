<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        // Create 30 deliveries with created_at randomized within the last 2 months
        $faker = \Faker\Factory::create();

        foreach (range(1, 30) as $_) {
            $created = $faker->dateTimeBetween('-2 months', 'now');
            Delivery::factory()->create([
                'status' => 'selesai',
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }
    }
}
