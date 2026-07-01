<?php

namespace App\Http\Controllers\Agents;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\HsiRole;
use Illuminate\Http\Request;

class AgentImportController extends Controller
{
    public function importGet(Request $request)
    {
        // 1. Decodificamos los datos recibidos (asumiendo que la extensión los envía como JSON stringified en los query params)
        $completed = json_decode(base64_decode($request->query('completed')), true);
        $personal = json_decode(base64_decode($request->query('personal')), true);
        $user = json_decode(base64_decode($request->query('user')), true);
        $roles = json_decode(base64_decode($request->query('roles')), true);

        // 2. Validación básica
        $dni = $completed['identificationNumber'] ?? null;
        if (! $dni) {
            return redirect()->route('agents.index')->with('error', 'Error al procesar datos de HSI');
        }

        $existingAgent = Agent::where('dni', $dni)->first();
        if ($existingAgent) {
            return redirect()->route('agents.show', $existingAgent->id);
        }

        // 3. Mapeo del género
        $genderDesc = strtolower($completed['gender']['description'] ?? '');
        $gender = match ($genderDesc) {
            'femenino' => AgentGender::FEMENINO->value,
            'masculino' => AgentGender::MASCULINO->value,
            'x' => AgentGender::X->value,
            default => AgentGender::PENDIENTE->value,
        };

        // 4. Crear el agente
        $agent = Agent::create([
            'first_name' => $completed['firstName'],
            'second_first_name' => $completed['middleName'] ?? null,
            'last_name' => $completed['lastName'],
            'second_last_name' => $completed['otherLastNames'] ?? null,
            'dni' => $dni,
            'gender' => $gender,
            'email' => $personal['email'] ?? null,
            'phone' => ($personal['phonePrefix'] ?? '').($personal['phoneNumber'] ?? ''),
            'person_id' => $completed['person']['id'] ?? null,
            'user_id' => $user['id'] ?? null,
            'user' => $user['username'] ?? null,
            'status' => AgentStatus::PENDIENTE,
        ]);

        // 5. Vincular roles (Buscamos por descripción exacta según tu lista)
        if (! empty($roles)) {
            // 1. Extraemos los nombres y los normalizamos (trim y lowercase) para evitar fallos por formato
            $roleNames = collect($roles)->map(function ($role) {
                return trim($role['roleDescription']);
            });

            // 2. Buscamos en tu base de datos usando un LIKE o comparando por nombre
            // Usamos whereIn con una consulta más flexible
            $roleIds = HsiRole::whereRaw('LOWER(name) IN (?)', [
                $roleNames->map(fn ($n) => mb_strtolower($n))->toArray(),
            ])->pluck('id');

            // 3. DEBUG: Si $roleIds está vacío, loguealo para saber qué está pasando
            if ($roleIds->isEmpty()) {
                \Log::warning('No se encontraron roles coincidentes para: '.$roleNames->implode(', '));
            }

            $agent->hsiRoles()->sync($roleIds);
        }

        return redirect()->route('agents.show', $agent->id)
            ->with('success', 'Agente creado mediante importación rápida.');
    }
}
