<?php

namespace App\Console\Commands;

use App\Enums\RegistrationScope;
use App\Enums\RegistrationType;
use App\Models\Agent;
use App\Models\AgentDocument;
use App\Models\AgentProfessionSpecialty;
use App\Models\HsiRoleAgent;
use App\Models\Occupation;
use App\Models\Registration;
use App\Models\ServiceBoss;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateLegacyData extends Command
{
    protected $signature = 'legacy:migrate-agents';

    protected $description = 'Migra datos legacy y descarga documentos HSI';

    public function handle()
    {
        $this->info('Iniciando migración desde el sistema Legacy...');

        $documentosMap = [
            'docsDni' => 4,
            'docsMatricula' => 3,
            'docsAnexoI' => 2,
            'docsAnexoII' => 1,
        ];

        $occupations = Occupation::all()->keyBy(function ($item) {
            return Str::slug($item->name);
        });

        $idSecretario = 1;
        $idAdministrativo = 2;

        $this->info('Cargando roles históricos...');
        $legacyRoles = DB::connection('legacy')
            ->table('usuarios_roles_hsi')
            ->where('id_rol', '!=', 4)
            ->get()
            ->groupBy('dni');

        $this->info('Procesando agentes y descargando archivos...');

        DB::connection('legacy')->table('personal')
            ->leftJoin('hsi', 'personal.dni', '=', 'hsi.dni')
            ->select(
                'personal.nombre', 'personal.apellido', 'personal.dni', 'personal.servicio_id',
                'personal.cargo', 'personal.mn', 'personal.mp',
                'hsi.estado as hsi_estado', // <--- CAMBIO AQUÍ: Traemos estado de tabla hsi
                'hsi.gender', 'hsi.mail', 'hsi.telefono', 'hsi.id_persona', 'hsi.id_usuario', 'hsi.nombre_usuario'
            )
            ->orderBy('personal.id')
            ->chunk(100, function ($viejos) use ($legacyRoles, $documentosMap, $occupations, $idSecretario, $idAdministrativo) {

                foreach ($viejos as $viejo) {

                    // Limpieza y Transformación Básica
                    $cleanDni = str_replace('.', '', $viejo->dni);

                    // Mapeo basado en tus Enums oficiales
                    $estadoLimpio = strtolower(trim($viejo->hsi_estado ?? ''));

                    $nuevoEstado = match ($estadoLimpio) {
                        'habilitado' => 'activo',
                        'disabled' => 'inactivo',
                        'working' => 'pendiente',
                        default => 'inactivo', // Fallback por seguridad
                    };

                    $genderValue = 'pendiente';
                    if (isset($viejo->gender)) {
                        if ($viejo->gender == '0') {
                            $genderValue = 'masculino';
                        } elseif ($viejo->gender == '1') {
                            $genderValue = 'femenino';
                        }
                    }

                    // 1. CREACIÓN DEL AGENTE
                    $agente = Agent::updateOrCreate(
                        ['dni' => $cleanDni],
                        [
                            'first_name' => $viejo->nombre,
                            'last_name' => $viejo->apellido,
                            'gender' => $genderValue,
                            'email' => $viejo->mail ?: mb_strtolower(($viejo->nombre_usuario ?? 'user').'@hsi.local'),
                            'phone' => $viejo->telefono ?: '0',
                            'status' => $nuevoEstado, // Ahora basado en tabla HSI
                            'service_id' => $viejo->servicio_id > 0 ? $viejo->servicio_id : null,
                            'person_id' => $viejo->id_persona ?: null,
                            'user_id' => $viejo->id_usuario ?: null,
                            'user' => $viejo->nombre_usuario ?: null,
                        ]
                    );

                    // 2. MIGRAR JEFATURAS
                    if (strtolower(trim($viejo->cargo)) === 'jefe de servicio' && $viejo->servicio_id > 0) {
                        ServiceBoss::firstOrCreate([
                            'agent_id' => $agente->id,
                            'service_id' => $viejo->servicio_id,
                        ]);
                    }

                    // 3. MIGRAR ROLES HSI
                    if (isset($legacyRoles[$viejo->dni])) {
                        foreach ($legacyRoles[$viejo->dni] as $rolViejo) {
                            HsiRoleAgent::firstOrCreate([
                                'agent_id' => $agente->id,
                                'hsi_role_id' => $rolViejo->id_rol,
                            ]);
                        }
                    }

                    // 4. MIGRAR PROFESIONES Y MATRÍCULAS
                    $slugCargo = Str::slug($viejo->cargo);
                    $professionId = $idSecretario;

                    if ($slugCargo === 'administrativo' || $slugCargo === 'secretario') {
                        $professionId = $idAdministrativo;
                    } elseif ($occupations->has($slugCargo)) {
                        $professionId = $occupations[$slugCargo]->id;
                    }

                    $profession = AgentProfessionSpecialty::firstOrCreate([
                        'agent_id' => $agente->id,
                        'profession_id' => $professionId,
                        'specialty_id' => null,
                    ]);

                    if (! empty($viejo->mn)) {
                        Registration::firstOrCreate([
                            'assignment_id' => $profession->id,
                            'number' => $viejo->mn,
                            // Usamos el .value del Enum de Scope y Type
                            'scope' => RegistrationScope::NACIONAL->value,
                            'type' => RegistrationType::PROFESION->value,
                        ]);
                    }

                    if (! empty($viejo->mp)) {
                        Registration::firstOrCreate([
                            'assignment_id' => $profession->id,
                            'number' => $viejo->mp,
                            // Usamos el .value del Enum de Scope y Type
                            'scope' => RegistrationScope::PROVINCIAL->value,
                            'type' => RegistrationType::PROFESION->value,
                        ]);
                    }

                    // 5. DESCARGA CIEGA DE DOCUMENTOS
                    foreach ($documentosMap as $sufijo => $typeId) {
                        $url = "http://10.10.211.50/SGH/app/hsiDocs/{$viejo->dni}-{$sufijo}.pdf";

                        try {
                            // Le ponemos un timeout para que no se quede colgado eternamente si la URL no responde
                            $response = Http::timeout(5)->get($url);

                            // Si el archivo existe (código 200), lo guardamos
                            if ($response->successful()) {
                                $filename = "agent_documents/{$agente->id}_{$sufijo}.pdf";
                                Storage::disk('public')->put($filename, $response->body());

                                AgentDocument::firstOrCreate([
                                    'agent_id' => $agente->id,
                                    'type_id' => $typeId,
                                ], [
                                    'path' => $filename,
                                ]);
                            }
                        } catch (\Exception $e) {
                            // Si tira un error 404 o timeout, lo ignora en silencio y pasa al siguiente
                        }
                    }
                }
            });

        $this->info('¡Migración y descarga de archivos completada exitosamente!');
    }
}
