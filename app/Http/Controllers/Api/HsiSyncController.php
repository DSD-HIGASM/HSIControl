<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HsiPatientSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HsiSyncController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos las estructuras (sin necesidad de pedir person_id por fuera)
        $validator = Validator::make($request->all(), [
            'completed' => 'required|array',
            'personal'  => 'required|array',
            'user'      => 'nullable|array',
            'roles'     => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Extraemos el DNI
        $dni = $request->input('completed.identificationNumber')
            ?? $request->input('completed.person.identificationNumber');

        if (! $dni) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el DNI en el payload.',
            ], 422);
        }

        // --- ACÁ ESTÁ LA SOLUCIÓN ---
        $completedData = $request->input('completed');
        
        // Como confirmaste que el ID correcto viene en la raíz (completedData['id']),
        // lo copiamos a la fuerza adentro de 'person' para que tu importador no falle.
        $completedData['person']['id'] = $completedData['id'];

        // 3. Insertamos o actualizamos pasando el array ya parcheado
        $sync = HsiPatientSync::updateOrCreate(
            [
                'dni' => $dni,
                'processed_at' => null,
            ],
            [
                'user_id'        => auth()->id(), 
                'is_global'      => $request->input('mode') === 'POST_GLOBAL',
                'completed_data' => $completedData, // <-- Guardamos el JSON corregido
                'personal_info'  => $request->input('personal'),
                'user_data'      => $request->input('user') ?? [],
                'roles_data'     => $request->input('roles') ?? [],
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Agente enviado a la bandeja de pendientes correctamente.',
            'sync_id' => $sync->id,
        ], 201);
    }
}
