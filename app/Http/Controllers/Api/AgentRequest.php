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
            'search' => ['required', 'string', 'max:255'],
        ]);

        $search = mb_strtolower(trim($filters['search']), 'UTF-8');
        $search = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ä', 'ë', 'ï', 'ö', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
            $search
        );
        $terms = preg_split('/\s+/', $search);
        $normalizeSql = function (string $column): string {
            return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER({$column}), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'), 'Á', 'a'), 'É', 'e'), 'Í', 'i'), 'Ó', 'o'), 'Ú', 'u')";
        };

        $query = Agent::query()
            ->with([
                'service',
                'agentProfessions.profession',
                'agentProfessions.specialty',
                'agentProfessions.registrations',
                'academicProfile',
            ]);

        $query->where(function ($query) use ($terms, $normalizeSql) {
            foreach ($terms as $term) {
                $numericTerm = preg_replace('/[^0-9]/', '', $term);

                $query->where(function ($termQuery) use ($term, $numericTerm, $normalizeSql) {
                    if ($numericTerm !== '') {
                        $termQuery->where('dni', 'like', "%{$numericTerm}%");
                    } else {
                        $termQuery->whereRaw('1 = 0');
                    }

                    $termQuery->orWhereRaw($normalizeSql('last_name').' LIKE ?', ["%{$term}%"])
                        ->orWhereRaw($normalizeSql('second_last_name').' LIKE ?', ["%{$term}%"])
                        ->orWhereRaw($normalizeSql('first_name').' LIKE ?', ["%{$term}%"])
                        ->orWhereRaw($normalizeSql('second_first_name').' LIKE ?', ["%{$term}%"])
                        ->orWhere('phone', 'like', '%'.($numericTerm !== '' ? $numericTerm : $term).'%');
                });
            }
        });

        $agents = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'data' => $agents,
            'total' => $agents->count(),
        ]);
    }
}