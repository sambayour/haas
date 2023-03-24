<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_email()
    {
        $payload = [
            'email' => 'samuelolubayo@yahoo.com',
            'password' => 'password',
        ];
        $response = $this->post('/api/v1/login', $payload)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'success' => true,
                'message' => 'Logged in Successfully',
            ]);
    }

    public function test_user_can_login_with_phone()
    {
        $payload = [
            'phone' => '08131631893',
            'password' => 'password',
        ];
        $response = $this->post('/api/v1/login', $payload)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'success' => true,
                'message' => 'Logged in Successfully',
            ]);
    }
}
