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
        // 1. Validamos las estructuras requeridas
        $validator = Validator::make($request->all(), [
            'completed' => 'required|array',
            'personal' => 'required|array',
            'user' => 'nullable|array',
            'roles' => 'nullable|array',
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

        // 3. Extraemos el user_id del bloque 'user' enviado por la extensión
        $userId = $request->input('user.id') ?? null;

        // 4. Insertamos o actualizamos pasando explícitamente el campo que SQLite te pide
        $sync = HsiPatientSync::updateOrCreate(
            [
                'dni' => $dni,
                'processed_at' => null,
            ],
            [
                'user_id'        => auth()->id(), // <-- Asignamos la columna nativa que fallaba
                'is_global'      => $request->input('mode') === 'POST_GLOBAL',
                'completed_data' => $request->input('completed'),
                'personal_info' => $request->input('personal'),
                'user_data' => $request->input('user') ?? [],
                'roles_data' => $request->input('roles') ?? [],
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Agente enviado a la bandeja de pendientes correctamente.',
            'sync_id' => $sync->id,
        ], 201);
    }
}
