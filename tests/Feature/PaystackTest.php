<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaystackTest extends TestCase
{
    public function test_that_only_paystack_ip_can_access_paystack_webhook(): void
    {
        $payload = [
            'dummy' => 'success.charge',
            'dummy_data' => 'CARD',
        ];
        $response = $this->post('/api/v1/paystack-webhook', $payload)
            ->assertStatus(403);
    }
}
