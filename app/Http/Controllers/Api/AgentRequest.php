<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentRequest extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'second_first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'second_last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $agents = Agent::query()
            ->with([
                'service',
                'agentProfessions.profession',
                'agentProfessions.specialty',
                'agentProfessions.registrations',
                'academicProfile',
            ])
            ->whereRaw('LOWER(first_name) = ?', [mb_strtolower($filters['first_name'])])
            ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($filters['last_name'])])
            ->when(
                filled($filters['second_first_name'] ?? null),
                fn ($query) => $query->whereRaw(
                    'LOWER(second_first_name) = ?',
                    [mb_strtolower($filters['second_first_name'])]
                )
            )
            ->when(
                filled($filters['second_last_name'] ?? null),
                fn ($query) => $query->whereRaw(
                    'LOWER(second_last_name) = ?',
                    [mb_strtolower($filters['second_last_name'])]
                )
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'data' => $agents,
            'total' => $agents->count(),
        ]);
    }
}