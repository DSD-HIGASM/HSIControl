<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legajo #{{ $agent->id }} - {{ mb_strtoupper($agent->last_name) }}, {{ mb_strtoupper($agent->first_name) }}
    </title>

    <!-- Cargamos los estilos de Tailwind -->
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            @page {
                margin: 1.5cm;
                size: A4 portrait;
            }

            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: white !important;
            }

            .avoid-break {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>

<body class="bg-white text-gray-900 font-sans max-w-4xl mx-auto py-8" onload="window.print();">

    @php
        // Pre-calculamos los datos de documentos igual que en el dashboard
        $mandatoryTypes = collect();
        foreach ($agent->hsiRoles as $role) {
            foreach ($role->documentTypes as $type) {
                if ($type->pivot->is_mandatory) {
                    $mandatoryTypes->push($type);
                }
            }
        }
        $mandatoryTypes = $mandatoryTypes->unique('id');

        $uploadedDocs = $agent->documents;
        $uploadedTypeIds = $uploadedDocs->pluck('type_id')->toArray();

        $missingMandatoryTypes = $mandatoryTypes->whereNotIn('id', $uploadedTypeIds);
        $uploadedMandatoryDocs = $uploadedDocs->whereIn('type_id', $mandatoryTypes->pluck('id')->toArray());
        $historicalDocs = $uploadedDocs->whereNotIn('type_id', $mandatoryTypes->pluck('id')->toArray());

        $statusValue = mb_strtoupper(is_object($agent->status) ? $agent->status->value : $agent->status);
    @endphp

    <!-- ENCABEZADO FORMAL -->
    <div class="border-b-2 border-gray-800 pb-4 mb-6 flex justify-between items-end avoid-break">
        <div>
            <h2 class="text-2xl font-bold uppercase tracking-widest text-gray-900">Legajo institucional de la HSI</h2>
            <p class="text-sm font-secondary text-gray-500 mt-1">Generado el {{ now()->format('d/m/Y H:i') }} -
                HSIControl</p>
        </div>
        <div class="text-right">
            <p class="font-bold text-2xl text-gray-900">N° {{ str_pad($agent->id, 5, '0', STR_PAD_LEFT) }}</p>
            <span
                class="inline-block mt-1 px-2 py-0.5 text-xs font-bold ring-1 ring-inset {{ $statusValue === 'ACTIVO' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-100 text-gray-700 ring-gray-500/20' }}">
                ESTADO: {{ $statusValue }}
            </span>
        </div>
    </div>

    <!-- 1. DATOS PERSONALES -->
    <div class="mb-6 avoid-break">
        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">1. Datos Personales
        </h3>
        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/30">
            <div class="grid grid-cols-4 gap-4 text-sm font-secondary">
                <div class="col-span-2">
                    <span
                        class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">Apellidos</span>
                    <span class="font-bold text-gray-900 uppercase">{{ $agent->last_name }}
                        {{ $agent->second_last_name }}</span>
                </div>
                <div class="col-span-2">
                    <span
                        class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">Nombres</span>
                    <span class="font-bold text-gray-900 uppercase">{{ $agent->first_name }}
                        {{ $agent->second_first_name }}</span>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">DNI</span>
                    <span class="font-bold text-gray-900">{{ number_format($agent->dni, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span
                        class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">Género</span>
                    <span
                        class="font-bold text-gray-900 uppercase">{{ is_object($agent->gender) ? $agent->gender->value : $agent->gender }}</span>
                </div>
                <div>
                    <span
                        class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">Teléfono</span>
                    <span class="font-bold text-gray-900">{{ $agent->phone ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">Email</span>
                    <span class="font-bold text-gray-900">{{ $agent->email ?? 'N/A' }}</span>
                </div>

                <div class="col-span-4 mt-2 pt-2 border-t border-gray-200">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-0.5">Servicio
                        Base (Planta)</span>
                    <span class="font-bold text-gray-900">{{ $agent->service->name ?? 'Sin asignar' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. PERFIL PROFESIONAL -->
    <div class="mb-6 avoid-break">
        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">2. Perfil Profesional y
            Matrículas</h3>
        @if($agent->agentProfessions->isNotEmpty())
            <table class="w-full text-sm font-secondary border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase">Profesión / Especialidad
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase">Matrículas Vinculadas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($agent->agentProfessions as $prof)
                        <tr class="bg-white">
                            <td class="px-4 py-3 align-top">
                                <span
                                    class="font-bold text-gray-900 uppercase block">{{ $prof->profession->name ?? 'N/A' }}</span>
                                <span
                                    class="text-[11px] text-gray-600 block mt-0.5">{{ $prof->specialty->name ?? 'Sin especialidad registrada' }}</span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @if($prof->registrations->isNotEmpty())
                                    <ul class="space-y-1">
                                        @foreach($prof->registrations as $reg)
                                            <li class="text-xs">
                                                <span class="font-bold text-gray-900">N° {{ $reg->number }}</span>
                                                <span
                                                    class="text-gray-500 uppercase">({{ is_object($reg->scope) ? $reg->scope->value : $reg->scope }}
                                                    / {{ is_object($reg->type) ? $reg->type->value : $reg->type }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-xs text-gray-400 italic">Sin matrículas cargadas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="border border-dashed border-gray-300 rounded-lg p-4 text-center">
                <p class="text-xs font-secondary text-gray-500 italic">No registra profesiones asignadas.</p>
            </div>
        @endif
    </div>

    <!-- 3. FORMACIÓN Y JEFATURAS (2 COLUMNAS) -->
    <div class="mb-10 grid grid-cols-2 gap-6 avoid-break">
        <!-- Residencias -->
        <div>
            <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">3. Formación /
                Residencias</h3>
            @if($agent->residencies->isNotEmpty())
                <div class="space-y-2">
                    @foreach($agent->residencies as $res)
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-sm text-gray-900 uppercase">{{ $res->program_name }}</span>
                                <span
                                    class="text-[10px] font-bold bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ mb_strtoupper($res->current_year) }}</span>
                            </div>
                            <span class="text-[11px] text-gray-600 font-secondary">Rotación HSI:
                                {{ $res->currentUnit->alias ?? $res->currentUnit->name ?? 'No vinculada' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="border border-dashed border-gray-300 rounded p-4 text-center h-full flex items-center justify-center">
                    <p class="text-xs font-secondary text-gray-500 italic">Sin residencias en curso.</p>
                </div>
            @endif
        </div>

        <!-- Jefaturas -->
        <div>
            <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">4. Jefaturas a
                cargo</h3>
            @if($agent->serviceBosses->isNotEmpty())
                <div class="space-y-2">
                    @foreach($agent->serviceBosses as $boss)
                        <div class="border border-gray-200 rounded p-3 bg-white flex items-center gap-2">
                            <span
                                class="font-bold text-sm text-gray-900 uppercase">{{ $boss->service->name ?? 'Servicio' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="border border-dashed border-gray-300 rounded p-4 text-center h-full flex items-center justify-center">
                    <p class="text-xs font-secondary text-gray-500 italic">No registra jefaturas activas.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- 4. CREDENCIALES Y ACCESOS HSI -->
    <div class="mb-6 avoid-break">
        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">5. Sistema Integrado
            (HSI)</h3>
        <div class="grid grid-cols-3 gap-4">

            <!-- Credenciales -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/30">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Credenciales de
                    Login</span>
                <div class="space-y-1.5 text-sm font-secondary">
                    <p><span class="text-gray-600">Person ID:</span> <span
                            class="font-bold text-gray-900">{{ $agent->person_id ?? 'N/A' }}</span></p>
                    <p><span class="text-gray-600">User ID:</span> <span
                            class="font-bold text-gray-900">{{ $agent->user_id ?? 'N/A' }}</span></p>
                    <p><span class="text-gray-600">Username:</span> <span
                            class="font-bold text-gray-900">{{ $agent->user ?? 'N/A' }}</span></p>
                </div>
            </div>

            <!-- Roles -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/30">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Roles
                    Asignados</span>
                @if($agent->hsiRoles->isNotEmpty())
                    <ul class="list-disc list-inside text-xs font-secondary text-gray-800 space-y-1">
                        @foreach($agent->hsiRoles as $role)
                            <li class="uppercase">{{ $role->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs font-secondary text-gray-500 italic">Sin roles asignados.</p>
                @endif
            </div>

            <!-- Unidades Jerárquicas -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/30">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Unidades
                    Jerárquicas</span>
                @if($agent->hierarchicalUnits->isNotEmpty())
                    <ul class="space-y-2 text-xs font-secondary">
                        @foreach($agent->hierarchicalUnits as $unit)
                            <li class="border-l-2 border-gray-400 pl-2">
                                <span
                                    class="font-bold text-gray-900 uppercase block">{{ $unit->alias ?? $unit->name ?? 'Unidad' }}</span>
                                <span class="text-[10px] text-gray-600">ID: {{ $unit->id }}
                                    {{ ($unit->pivot->responsible ?? false) ? '| RESPONSABLE' : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs font-secondary text-gray-500 italic">No vinculado a unidades.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- 5. LEGAJO DIGITAL / DOCUMENTACIÓN -->
    <div class="mb-6 avoid-break">
        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">6. Estado de Legajo
            Digital (Matriz Requerida)</h3>

        @if($missingMandatoryTypes->isEmpty() && $uploadedMandatoryDocs->isEmpty() && $historicalDocs->isEmpty())
            <div class="border border-dashed border-gray-300 rounded-lg p-4 text-center">
                <p class="text-xs font-secondary text-gray-500 italic">El agente no posee roles que exijan documentación
                    obligatoria y no hay archivos anexos.</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-6">
                <!-- Documentos Subidos (OK) -->
                <div>
                    <span class="text-[10px] font-bold text-green-700 uppercase tracking-wider block mb-2">Verificados e
                        Ingresados</span>
                    @if($uploadedMandatoryDocs->isNotEmpty() || $historicalDocs->isNotEmpty())
                        <ul class="space-y-2 text-sm font-secondary">
                            @foreach($uploadedMandatoryDocs as $doc)
                                <li class="flex items-start gap-2 border border-gray-200 rounded p-2 bg-white">
                                    <span
                                        class="font-bold text-gray-800 text-xs uppercase">{{ $doc->type->name ?? 'Documento' }}</span>
                                </li>
                            @endforeach
                            @foreach($historicalDocs as $doc)
                                <li class="flex items-start gap-2 border border-gray-200 rounded p-2 bg-white">
                                    <span
                                        class="font-bold text-gray-600 text-xs uppercase">{{ $doc->type->name ?? $doc->other_type ?? 'Anexo Extra' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs font-secondary text-gray-500 italic">No hay archivos digitalizados.</p>
                    @endif
                </div>

                <!-- Documentos Faltantes (Pendientes) -->
                <div>
                    <span class="text-[10px] font-bold text-red-700 uppercase tracking-wider block mb-2">Pendientes</span>
                    @if($missingMandatoryTypes->isNotEmpty())
                        <ul class="space-y-2 text-sm font-secondary">
                            @foreach($missingMandatoryTypes as $type)
                                <li class="flex items-start gap-2 border border-red-200 rounded p-2 bg-red-50">
                                    <span class="font-bold text-red-700 text-xs uppercase">{{ $type->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="border border-gray-200 rounded p-2 bg-green-50 text-center">
                            <p class="text-xs font-bold font-secondary text-green-700">Matriz documental completa.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- 6. NOTACIONES -->
    <div class="mb-6 avoid-break">
        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-300 pb-1 mb-3">6. Notaciones</h3>
        @if($agent->notes->isNotEmpty())
            <div class="space-y-2">
                @foreach($agent->notes as $note)
                    <div class="border border-gray-200 rounded p-3 bg-white">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-sm text-gray-900 uppercase">{{ $note->title ?? 'Nota' }}</span>
                            <span
                                class="text-[10px] font-bold bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $note->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <span class="text-[11px] text-gray-600 font-secondary">{{ $note->content }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="border border-dashed border-gray-300 rounded p-4 text-center h-full flex items-center justify-center">
                <p class="text-xs font-secondary text-gray-500 italic">No hay notas.</p>
            </div>
        @endif
    </div>
</body>

</html>
