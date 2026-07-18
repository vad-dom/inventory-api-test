<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiKeyAuthenticationTest extends TestCase
{
    public function test_request_without_api_key_returns_unauthorized_response(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'UNAUTHORIZED')
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);
    }
}
