<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Enums\AgentStatus;

class AgentPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Agent::with([
            'service', 
            'agentProfessions.profession', 
            'agentProfessions.specialty',
            'documents',
            'hsiRoles.documentTypes'
        ])->latest();

        // Aplicamos los mismos filtros que vienen por GET (query string)
        if ($request->filled('status')) {
            if ($request->status === 'Activos') {
                $query->where('status', AgentStatus::ACTIVO);
            } elseif ($request->status === 'Inactivos') {
                $query->where('status', AgentStatus::INACTIVO);
            }
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('profession_id')) {
            $query->whereHas('agentProfessions', function ($q) use ($request) {
                $q->where('profession_id', $request->profession_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dni', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%");
            });
        }

        $agents = $query->get(); // Obtenemos todos sin paginar para imprimir

        return view('agents.print', compact('agents', 'request'));
    }
}