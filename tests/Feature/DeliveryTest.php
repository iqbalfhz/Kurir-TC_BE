<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_delivery_with_photo()
    {
    Storage::fake('public');
    Storage::fake('local');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('doc.jpg');

        $payload = [
            'sender_name' => 'Alice',
            'receiver_name' => 'Bob',
            'address' => 'Jl. Contoh No 1',
            'notes' => 'Handle with care',
            'photo' => $file,
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/deliveries', $payload);

    $response->assertStatus(201);
        $this->assertDatabaseHas('deliveries', ['sender_name' => 'Alice', 'receiver_name' => 'Bob']);

        $delivery = $response->json();
        $this->assertArrayHasKey('photo', $delivery);
        Storage::disk('public')->assertExists($delivery['photo']);
    }
}
