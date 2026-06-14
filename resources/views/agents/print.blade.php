<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Padrón de Personal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            @page { size: landscape; margin: 10mm; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8 font-secondary">
    
    <div class="max-w-[95rem] mx-auto bg-white p-8 rounded-lg shadow-sm print:shadow-none print:p-0">
        
        <div class="flex justify-between items-start mb-6 border-b border-gray-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 uppercase">Padrón de Personal</h1>
                <p class="text-sm text-gray-500">
                    Filtros aplicados: 
                    Estado: <span class="font-bold">{{ $request->status ?? 'Todos' }}</span> | 
                    Búsqueda: <span class="font-bold">{{ $request->search ?? 'Ninguna' }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">Generado el {{ now()->format('d/m/Y H:i') }} hs</p>
            </div>
            <button onclick="window.print()" class="no-print bg-brand-cyan text-white px-4 py-2 rounded font-bold shadow-sm hover:bg-brand-cyan-dark">
                Imprimir Reporte
            </button>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 uppercase">
                    <th class="p-2 border border-gray-300">DNI</th>
                    <th class="p-2 border border-gray-300">Agente</th>
                    <th class="p-2 border border-gray-300">Servicio Base</th>
                    <th class="p-2 border border-gray-300">Profesiones</th>
                    <th class="p-2 border border-gray-300">Documentos Faltantes</th>
                    <th class="p-2 border border-gray-300">Roles HSI</th>
                    <th class="p-2 border border-gray-300">Estado HSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agents as $agent)
                    <tr class="border-b border-gray-200">
                        <td class="p-2 border border-gray-300">{{ number_format($agent->dni, 0, ',', '.') }}</td>
                        <td class="p-2 border border-gray-300 font-bold uppercase">{{ $agent->last_name }}, {{ $agent->first_name }}</td>
                        <td class="p-2 border border-gray-300">{{ $agent->service->name ?? 'Sin Servicio' }}</td>
                        <td class="p-2 border border-gray-300">
                            @foreach($agent->agentProfessions as $ap)
                                {{ $ap->profession->name ?? '' }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                        <td class="p-2 border border-gray-300 text-red-600">
                            {{ implode(', ', $agent->document_status['missing_docs'] ?? []) ?: 'COMPLETO' }}
                        </td>
                        <td class="p-2 border border-gray-300">
                            @foreach($agent->hsiRoles as $role)
                                {{ $role->name }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                        <td class="p-2 border border-gray-300">{{ $agent->hsi_access_status['label'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</body>
</html>