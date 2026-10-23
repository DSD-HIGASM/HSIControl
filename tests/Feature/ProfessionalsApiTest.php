<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AcademicAgent;
use App\Models\AgentProfessionSpecialty;
use App\Models\Occupation;
use App\Models\Registration;
use App\Models\Service;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_request_searches_professionals_with_one_flexible_query(): void
    {
        $this->actingAs(User::create([
            'dni' => '10000001',
            'password' => 'password',
        ]));

        $agent = Agent::create([
            'first_name' => 'Ana',
            'second_first_name' => 'María',
            'last_name' => 'Pérez',
            'second_last_name' => 'Gómez',
            'dni' => 12345678,
            'gender' => 'femenino',
            'email' => 'ana@example.test',
            'phone' => '2215551234',
            'status' => 'activo',
        ]);

        Agent::create([
            'first_name' => 'Ana',
            'last_name' => 'Gómez',
            'dni' => 87654321,
            'gender' => 'femenino',
            'email' => 'otra@example.test',
            'phone' => '2215555678',
            'status' => 'activo',
        ]);

        $this->getJson('/api/professionals?search=pez%20ana')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $agent->id)
            ->assertJsonPath('data.0.first_name', 'Ana')
            ->assertJsonPath('data.0.agent_professions', []);
    }

    public function test_professional_search_requires_a_search_string(): void
    {
        $this->actingAs(User::create([
            'dni' => '10000001',
            'password' => 'password',
        ]));

        $this->getJson('/api/professionals')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }

    public function test_professional_search_requires_authentication(): void
    {
        $this->getJson('/api/professionals?search=Ana%20Pérez')
            ->assertUnauthorized();
    }

    public function test_professional_search_accepts_a_sanctum_bearer_token(): void
    {
        $user = User::create([
            'dni' => '10000002',
            'password' => 'password',
        ]);

        $this->withToken($user->createToken('api-test')->plainTextToken)
            ->getJson('/api/professionals?search=Ana%20Pérez')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('total', 0);
    }

    public function test_all_name_parts_can_be_matched_in_any_order(): void
    {
        $this->actingAs(User::create([
            'dni' => '10000003',
            'password' => 'password',
        ]));

        $matchingAgent = $this->createAgent([
            'dni' => 12345678,
            'second_first_name' => 'María',
            'second_last_name' => 'Gómez',
        ]);
        $this->createAgent([
            'dni' => 87654321,
            'second_first_name' => 'Elena',
            'second_last_name' => 'López',
        ]);

        $this->getJson('/api/professionals?search=Gómez%20mar%20ana')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $matchingAgent->id);
    }

    public function test_professional_search_returns_all_professional_relationships(): void
    {
        $this->actingAs(User::create([
            'dni' => '10000004',
            'password' => 'password',
        ]));

        $service = Service::create(['name' => 'Clínica']);
        $profession = Occupation::create(['name' => 'Médica']);
        $specialty = Speciality::create(['name' => 'Cardiología']);
        $agent = $this->createAgent([
            'dni' => 12345678,
            'service_id' => $service->id,
        ]);
        $assignment = AgentProfessionSpecialty::create([
            'agent_id' => $agent->id,
            'profession_id' => $profession->id,
            'specialty_id' => $specialty->id,
        ]);
        Registration::create([
            'assignment_id' => $assignment->id,
            'number' => '12345',
            'scope' => 'provincial',
            'type' => 'profesion',
        ]);
        AcademicAgent::create([
            'agent_id' => $agent->id,
            'unlp_email' => 'ana@unlp.edu.ar',
            'personal_email' => 'ana@example.test',
            'facultad' => 'Ciencias Médicas',
            'carrera_catedra' => 'Medicina',
            'materia' => 'Cardiología',
            'cargo_docente' => 'Jefa de trabajos prácticos',
            'autoridad_firmante_nombre' => 'Autoridad',
            'autoridad_firmante_cargo' => 'Decana',
            'expires_at' => '2027-12-31',
        ]);

        $this->getJson('/api/professionals?search=Ana%20Pérez')
            ->assertOk()
            ->assertJsonPath('data.0.service.name', 'Clínica')
            ->assertJsonPath('data.0.agent_professions.0.profession.name', 'Médica')
            ->assertJsonPath('data.0.agent_professions.0.specialty.name', 'Cardiología')
            ->assertJsonPath('data.0.agent_professions.0.registrations.0.number', '12345')
            ->assertJsonPath('data.0.academic_profile.facultad', 'Ciencias Médicas');
    }

    public function test_professional_search_requires_a_string_query(): void
    {
        $this->actingAs(User::create([
            'dni' => '10000005',
            'password' => 'password',
        ]));

        $this->getJson('/api/professionals?search[]=Ana')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);

        $this->getJson('/api/professionals?search=')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }

    public function test_search_matches_partial_document_and_phone_numbers(): void
    {
        $this->actingAs(User::create([
            'dni' => '10000006',
            'password' => 'password',
        ]));

        $agent = $this->createAgent([
            'dni' => 12345678,
            'phone' => '2215551234',
        ]);

        $this->getJson('/api/professionals?search=3456')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $agent->id);

        $this->getJson('/api/professionals?search=5551')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $agent->id);
    }

    private function createAgent(array $overrides = []): Agent
    {
        return Agent::create(array_merge([
            'first_name' => 'Ana',
            'last_name' => 'Pérez',
            'dni' => 12345678,
            'gender' => 'femenino',
            'email' => 'ana@example.test',
            'phone' => '2215551234',
            'status' => 'activo',
        ], $overrides));
    }
}
