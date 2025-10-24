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
    // only one status is used now: 'selesai' (Selesai)
    $statuses = ['selesai'];

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? null,
            'sender_name' => $this->faker->name(),
            'receiver_name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement($statuses),
            // simulate stored file path (must not be null anymore)
            'photo' => $this->faker->bothify('deliveries/##########.jpg'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
