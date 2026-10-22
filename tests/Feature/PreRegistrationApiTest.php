<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PreRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_request_creates_a_pre_registration_from_the_json_payload(): void
    {
        $user = User::create([
            'dni' => '10000020',
            'password' => 'password',
        ]);
        $payload = [
            'professional' => ['name' => 'Ana Pérez'],
            'is_global' => true,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/pre-register', $payload)
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertTrue(Str::isUuid($response->json('token')));
        $this->assertDatabaseHas('pre_registrations', [
            'id' => $response->json('token'),
            'user_id' => $user->id,
            'is_global' => true,
            'data' => json_encode($payload),
        ]);
    }

    public function test_pre_registration_defaults_to_local_scope(): void
    {
        $user = User::create([
            'dni' => '10000021',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/pre-register', ['dni' => 12345678])
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('pre_registrations', [
            'id' => $response->json('token'),
            'user_id' => $user->id,
            'is_global' => false,
        ]);
    }

    public function test_pre_registration_requires_authentication(): void
    {
        $this->postJson('/api/pre-register', ['dni' => 12345678])
            ->assertUnauthorized();
    }
}
