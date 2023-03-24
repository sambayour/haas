<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentTest extends TestCase
{

    public function test_guest_cannot_book_appointment(): void
    {
        $payload = [
            'notes' => 'Ulcer',
            'patient_id' => '98c3d21e-3b73-49c7-b352-4f43101ced4c',
            'order_ref' => random_int(100000, 999999),
        ];

        $response = $this->post('/api/v1/appointments', $payload)
            ->assertStatus(500);
    }

    public function test_user_can_book_appointment(): void
    {

        $user = User::factory()->create();
        \Log::info('factory test user' . $user);

        $payload = [
            'notes' => 'Ulcer',
            'patient_id' => $user->id,
            'order_ref' => random_int(100000, 999999),
        ];

        Sanctum::actingAs($user, ['0']);

        $response = $this->post('/api/v1/appointments', $payload)
            ->assertStatus(201)
            ->assertJson([
                "status" => 'ok',
                "success" => true,
                "message" => "Appointment added succesfully",
            ]);

    }
}
