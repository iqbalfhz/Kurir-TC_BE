<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition()
    {
        $statuses = ['pending', 'picked_up', 'delivered', 'failed'];

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? null,
            'sender_name' => $this->faker->name(),
            'receiver_name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement($statuses),
            // simulate stored file path or null
            'photo' => $this->faker->optional(0.5)->bothify('deliveries/##########.jpg'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
