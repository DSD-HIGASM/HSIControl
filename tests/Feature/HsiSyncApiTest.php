<?php

namespace Tests\Feature;

use App\Models\HsiPatientSync;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HsiSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_request_stores_a_sync_and_normalizes_the_person_id(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/hsi-sync', [
                'completed' => [
                    'id' => 9876,
                    'identificationNumber' => '12345678',
                ],
                'personal' => ['name' => 'Ana'],
                'mode' => 'POST_GLOBAL',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Agente enviado a la bandeja de pendientes correctamente.')
            ->assertJsonStructure(['sync_id']);

        $sync = HsiPatientSync::firstOrFail();
        $this->assertSame('12345678', $sync->dni);
        $this->assertSame(9876, $sync->completed_data['person']['id']);
        $this->assertSame(['name' => 'Ana'], $sync->personal_info);
        $this->assertSame([], $sync->user_data);
        $this->assertSame([], $sync->roles_data);
        $this->assertTrue($sync->is_global);
        $this->assertSame($user->id, $sync->user_id);
    }

    public function test_hsi_sync_accepts_the_dni_from_the_nested_person(): void
    {
        $this->actingAs($this->createUser())
            ->postJson('/api/hsi-sync', [
                'completed' => [
                    'id' => 456,
                    'person' => ['identificationNumber' => '87654321'],
                ],
                'personal' => ['name' => 'Ana'],
                'user' => ['username' => 'ana'],
                'roles' => ['roles' => []],
                'mode' => 'POST_LOCAL',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('hsi_patient_data_sync', [
            'dni' => '87654321',
            'is_global' => false,
        ]);
    }

    public function test_hsi_sync_updates_an_existing_unprocessed_record_for_the_same_dni(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $payload = [
            'completed' => [
                'id' => 123,
                'identificationNumber' => '12345678',
            ],
            'personal' => ['name' => 'Primera carga'],
        ];

        $firstResponse = $this->postJson('/api/hsi-sync', $payload)->assertCreated();
        $firstId = $firstResponse->json('sync_id');

        $payload['personal'] = ['name' => 'Carga actualizada'];
        $secondResponse = $this->postJson('/api/hsi-sync', $payload)->assertCreated();

        $this->assertSame($firstId, $secondResponse->json('sync_id'));
        $this->assertDatabaseCount('hsi_patient_data_sync', 1);
        $this->assertSame(
            ['name' => 'Carga actualizada'],
            HsiPatientSync::findOrFail($firstId)->personal_info
        );
    }

    public function test_hsi_sync_rejects_missing_or_non_array_required_sections(): void
    {
        $this->actingAs($this->createUser());

        $this->postJson('/api/hsi-sync', [])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['completed', 'personal']]);

        $this->postJson('/api/hsi-sync', [
            'completed' => 'invalid',
            'personal' => ['name' => 'Ana'],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['completed']]);
    }

    public function test_hsi_sync_rejects_payloads_without_a_dni(): void
    {
        $this->actingAs($this->createUser())
            ->postJson('/api/hsi-sync', [
                'completed' => ['id' => 123],
                'personal' => ['name' => 'Ana'],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'No se encontró el DNI en el payload.');

        $this->assertDatabaseCount('hsi_patient_data_sync', 0);
    }

    public function test_hsi_sync_requires_authentication(): void
    {
        $this->postJson('/api/hsi-sync', [
            'completed' => ['id' => 123, 'identificationNumber' => '12345678'],
            'personal' => [],
        ])->assertUnauthorized();
    }

    private function createUser(): User
    {
        return User::create([
            'dni' => '10000010',
            'password' => 'password',
        ]);
    }
}
