<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Agent;

class AgentController extends Controller
{
    /**
     * Muestra la vista de impresión de la ficha completa del agente.
     */
    public function printFicha(Agent $agent)
    {
        // Cargamos todas las relaciones necesarias para evitar problemas de N+1 en la vista de impresión
        $agent->load([
            'service',
            'agentProfessions.profession',
            'agentProfessions.specialty',
            'agentProfessions.registrations',
            'documents.type',
            'hsiRoles.documentTypes',
            'residencies.currentUnit',
            'serviceBosses.service',
            'hierarchicalUnits'
        ]);

        return view('agents.printAgent', compact('agent'));
    }
}