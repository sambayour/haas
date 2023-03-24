<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationTest extends TestCase
{

    public function test_new_users_can_register()
    {
        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'samuelolubayo@yandex.com',
            'phone' => '8131631893',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/api/v1/signup', $payload)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'success' => true,
                'message' => 'Registration Successful',
            ]);

    }
}
