<?php

namespace App\Services;

use App\Models\MikySetting;
use App\Models\MikyTerminal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MikyMeshService
{
    protected string $token;

    public function __construct()
    {
        $this->token = (string) MikySetting::get('backend_token', config('services.miky.token', ''));
    }

    protected function client(int $timeout = 3)
    {
        return Http::timeout($timeout)
            ->withToken($this->token)
            ->acceptJson();
    }

    /**
     * Consulta el estado de una terminal (/status - no requiere token)
     */
    public function getStatus(MikyTerminal $terminal): ?array
    {
        try {
            $response = Http::timeout(2)->get("http://{$terminal->ip}:5000/status");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            // Terminal inalcanzable u offline
        }

        return null;
    }

    /**
     * Envía comandos de control (/control)
     */
    public function control(MikyTerminal $terminal, string $action, array $params = []): bool
    {
        try {
            $payload = array_merge(['accion' => $action], $params);
            $response = $this->client()->post("http://{$terminal->ip}:5000/control", $payload);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning("Error al enviar comando {$action} a {$terminal->ip}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia la URL de inicio y reinicia Chromium (/set_startup)
     */
    public function setStartup(MikyTerminal $terminal, string $url): bool
    {
        try {
            $response = $this->client()->post("http://{$terminal->ip}:5000/set_startup", [
                'url' => $url,
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning("Error al configurar URL de inicio en {$terminal->ip}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene captura de pantalla en Base64 (/screenshot)
     */
    public function getScreenshot(MikyTerminal $terminal): ?string
    {
        try {
            $response = $this->client(5)->get("http://{$terminal->ip}:5000/screenshot?t=" . time());
            if ($response->successful()) {
                return $response->json('image');
            }
        } catch (\Throwable $e) {
            // Terminal offline o error de scrot
        }

        return null;
    }

    /**
     * Despliegue Masivo OTA (agent.py)
     */
    public function deployOta(string $repoUrl): int
    {
        $terminals = MikyTerminal::where('is_active', true)->get();
        $dispatched = 0;

        foreach ($terminals as $t) {
            if ($this->control($t, 'update_agent', ['url' => $repoUrl])) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    /**
     * Despliegue Masivo de Branding (Logo Hospital + SVG Ministerio)
     */
    public function deployLogos(string $hospitalLogoUrl, string $otaUrl): int
    {
        $minUrl = str_replace('agent.py', 'ministerio.svg', $otaUrl);
        $terminals = MikyTerminal::where('is_active', true)->get();
        $dispatched = 0;

        foreach ($terminals as $t) {
            if ($this->control($t, 'update_logos', [
                'url_hospital' => $hospitalLogoUrl,
                'url_ministerio' => $minUrl,
            ])) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    /**
     * Puente Wake-On-LAN: usa un nodo encendido para emitir el paquete mágico
     */
    public function wakeOnLanBridge(MikyTerminal $targetTerminal): bool
    {
        if (empty($targetTerminal->mac) || $targetTerminal->mac === '00:00:00:00:00:00') {
            return false;
        }

        $aliveBridge = MikyTerminal::where('is_active', true)
            ->where('id', '!=', $targetTerminal->id)
            ->get()
            ->first(fn ($t) => $this->getStatus($t) !== null);

        if (! $aliveBridge) {
            return false;
        }

        return $this->control($aliveBridge, 'wol', ['mac' => $targetTerminal->mac]);
    }

    /**
     * Importar base de datos desde un nodo Miky hacia MySQL
     */
    public function pullFromNode(string $ip): array
    {
        $cleanIp = trim(preg_replace('#^https?://#', '', explode(':', $ip)[0]));

        try {
            $response = $this->client(5)->get("http://{$cleanIp}:5000/sync");

            if ($response->status() === 401) {
                return ['success' => false, 'message' => 'Contraseña/Token Auth rechazado por la terminal.'];
            }

            if (! $response->successful()) {
                return ['success' => false, 'message' => "No se pudo conectar con {$cleanIp}:5000."];
            }

            $data = $response->json();
            $db = $data['db'] ?? [];

            if (! empty($db['sectors'])) {
                MikySetting::set('sectors', json_encode(array_values($db['sectors'])));
            }
            if (isset($db['globalDefault'])) {
                MikySetting::set('global_default_url', $db['globalDefault']);
            }
            if (isset($db['maintUrl'])) {
                MikySetting::set('maint_url', $db['maintUrl']);
            }
            if (isset($db['tgToken'])) {
                MikySetting::set('tg_token', $db['tgToken']);
            }
            if (isset($db['tgChat'])) {
                MikySetting::set('tg_chat', $db['tgChat']);
            }
            if (isset($db['otaUrl'])) {
                MikySetting::set('ota_url', $db['otaUrl']);
            }
            if (isset($db['logoUrl'])) {
                MikySetting::set('logo_url', $db['logoUrl']);
            }

            $importedCount = 0;
            $pcs = $db['pcs'] ?? [];

            foreach ($pcs as $pc) {
                if (empty($pc['ip'])) continue;

                MikyTerminal::updateOrCreate(
                    ['ip' => trim($pc['ip'])],
                    [
                        'name' => $pc['name'] ?? 'Terminal ' . $pc['ip'],
                        'mac' => $pc['mac'] ?? null,
                        'sector' => $pc['sector'] ?? 'General',
                        'resolution' => $pc['resolution'] ?? 'auto',
                        'custom_buttons' => $pc['customButtons'] ?? [],
                        'cron' => $pc['cron'] ?? null,
                        'is_active' => true,
                    ]
                );
                $importedCount++;
            }

            return [
                'success' => true,
                'count' => $importedCount,
                'leader' => $data['current_leader'] ?? null,
                'message' => "Se importaron {$importedCount} terminales y la configuración de Miky7 con éxito.",
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    /**
     * Replicar MySQL hacia todas las terminales (/sync)
     */
    public function syncCluster(): void
    {
        $terminals = MikyTerminal::where('is_active', true)->get();
        $sectors = json_decode(MikySetting::get('sectors', '["General"]'), true);

        $payload = [
            'pcs' => $terminals->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'ip' => $t->ip,
                    'mac' => $t->mac ?? '',
                    'sector' => $t->sector,
                    'resolution' => $t->resolution ?? 'auto',
                    'customButtons' => $t->custom_buttons ?? [],
                    'cron' => $t->cron,
                ];
            })->toArray(),
            'sectors' => $sectors,
            'globalDefault' => (string) MikySetting::get('global_default_url', ''),
            'maintUrl' => (string) MikySetting::get('maint_url', 'local'),
            'tgToken' => (string) MikySetting::get('tg_token', ''),
            'tgChat' => (string) MikySetting::get('tg_chat', ''),
            'otaUrl' => (string) MikySetting::get('ota_url', ''),
            'logoUrl' => (string) MikySetting::get('logo_url', ''),
        ];

        foreach ($terminals as $terminal) {
            try {
                $this->client(2)->post("http://{$terminal->ip}:5000/sync", $payload);
            } catch (\Throwable $e) {
                // Silencioso
            }
        }
    }
}