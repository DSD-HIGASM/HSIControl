<?php

namespace App\Livewire\Miky;

use App\Models\MikySetting;
use App\Models\MikyTerminal;
use App\Services\MikyMeshService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MikyDashboard extends Component
{
    public string $view = 'grid'; // 'grid' | 'list'
    public string $activeSector = 'Todos';
    public string $search = '';

    // Modales
    public bool $showTerminalModal = false;
    public bool $showConfigModal = false;
    public bool $showImportModal = false;
    public bool $showScreenshotModal = false;
    public bool $showCronModal = false;

    // Screenshot expandido
    public ?int $modalTerminalId = null;
    public ?string $modalTerminalName = null;

    // Edición de Terminal
    public ?int $editing_terminal_id = null;
    public string $term_name = '';
    public string $term_ip = '';
    public string $term_mac = '';
    public string $term_sector = 'General';
    public string $term_resolution = 'auto';
    public array $term_custom_buttons = [];

    // Gestor de Energía (CRON)
    public ?int $cron_terminal_id = null;
    public string $cron_pc_name = '';
    public string $cron_on = '06:00';
    public string $cron_off = '20:00';
    public bool $has_active_cron = false;

    // Importación
    public string $import_node_ip = '';

    // Configuración Global
    public string $cfg_token = '';
    public string $cfg_global_url = '';
    public string $cfg_maint_url = 'local';
    public string $cfg_tg_token = '';
    public string $cfg_tg_chat = '';
    public string $cfg_ota_url = '';
    public string $cfg_logo_url = '';
    public array $sectors = ['General'];
    public string $new_sector = '';

    // Estados dinámicos de hardware
    public array $terminalStates = [];

    protected function rules()
    {
        return [
            'term_name' => 'required|string|max:100',
            'term_ip' => 'required|ip',
            'term_mac' => 'nullable|string|max:30',
            'term_sector' => 'required|string',
            'term_resolution' => 'required|string',
        ];
    }

    public function mount()
    {
        $this->loadSettings();
        $this->pollTerminals();
    }

    public function loadSettings(): void
    {
        $this->cfg_token = (string) MikySetting::get('backend_token', '');
        $this->cfg_global_url = (string) MikySetting::get('global_default_url', '');
        $this->cfg_maint_url = (string) MikySetting::get('maint_url', 'local');
        $this->cfg_tg_token = (string) MikySetting::get('tg_token', '');
        $this->cfg_tg_chat = (string) MikySetting::get('tg_chat', '');
        $this->cfg_ota_url = (string) MikySetting::get('ota_url', '');
        $this->cfg_logo_url = (string) MikySetting::get('logo_url', '');
        $this->sectors = json_decode(MikySetting::get('sectors', '["General"]'), true);
    }

    public function pollTerminals(): void
    {
        $service = app(MikyMeshService::class);
        $terminals = MikyTerminal::where('is_active', true)->get();

        foreach ($terminals as $term) {
            $status = $service->getStatus($term);
            if ($status) {
                $this->terminalStates[$term->id] = [
                    'is_online' => true,
                    'cpu' => (int) round($status['cpu'] ?? 0),
                    'vol' => $status['vol'] ?? '0',
                    'url' => $status['url'] ?? '',
                    'kiosk_lock' => $status['kiosk_lock'] ?? true,
                    'leader' => $status['leader'] ?? null,
                ];

                if (! empty($status['mac']) && empty($term->mac)) {
                    $term->update(['mac' => $status['mac']]);
                }
            } else {
                $this->terminalStates[$term->id] = [
                    'is_online' => false,
                    'cpu' => 0,
                    'vol' => 'Err',
                    'url' => 'Offline',
                    'kiosk_lock' => true,
                    'leader' => null,
                ];
            }
        }
    }

    public function openScreenshotModal(int $terminalId): void
    {
        $terminal = MikyTerminal::findOrFail($terminalId);
        $this->modalTerminalId = $terminal->id;
        $this->modalTerminalName = $terminal->name;
        $this->showScreenshotModal = true;
    }

    // --- ACCIONES DE CONTROL ---
    public function executeCommand(int $terminalId, string $action, array $params = []): void
    {
        $terminal = MikyTerminal::findOrFail($terminalId);
        $success = app(MikyMeshService::class)->control($terminal, $action, $params);

        if ($success) {
            session()->flash('status', "Comando {$action} ejecutado con éxito en {$terminal->name}.");
            $this->pollTerminals();
        } else {
            session()->flash('error', "No se pudo comunicar con {$terminal->name}.");
        }
    }

    public function setStartupUrl(int $terminalId, string $url): void
    {
        $terminal = MikyTerminal::findOrFail($terminalId);
        $success = app(MikyMeshService::class)->setStartup($terminal, $url);

        if ($success) {
            session()->flash('status', "URL de inicio actualizada en {$terminal->name}.");
            $this->pollTerminals();
        }
    }

    public function launchGlobalUrl(): void
    {
        if (empty($this->cfg_global_url)) {
            session()->flash('error', 'No hay una URL Global configurada.');
            return;
        }

        $terminals = MikyTerminal::where('is_active', true)->get();
        $service = app(MikyMeshService::class);

        foreach ($terminals as $t) {
            $service->setStartup($t, $this->cfg_global_url);
        }

        session()->flash('status', 'URL global desplegada en todas las pantallas.');
        $this->pollTerminals();
    }

    public function globalControl(string $action): void
    {
        $terminals = MikyTerminal::where('is_active', true)->get();
        $service = app(MikyMeshService::class);

        foreach ($terminals as $t) {
            $service->control($t, $action);
        }

        session()->flash('status', "Comando masivo {$action} enviado al clúster.");
        $this->pollTerminals();
    }

    public function toggleKioskLock(int $terminalId, bool $state): void
    {
        $this->executeCommand($terminalId, 'toggle_kiosk', ['state' => $state]);
    }

    public function triggerWakeOnLan(int $terminalId): void
    {
        $terminal = MikyTerminal::findOrFail($terminalId);
        $success = app(MikyMeshService::class)->wakeOnLanBridge($terminal);

        if ($success) {
            session()->flash('status', "Paquete mágico Wake-On-LAN emitido a {$terminal->name}.");
        } else {
            session()->flash('error', "No hay terminales encendidas disponibles para hacer puente WOL.");
        }
    }

    // --- GESTOR DE ENERGÍA (CRON) ---
    public function openCronModal(int $terminalId): void
    {
        $terminal = MikyTerminal::findOrFail($terminalId);
        $this->cron_terminal_id = $terminalId;
        $this->cron_pc_name = $terminal->name;

        if (! empty($terminal->cron)) {
            $this->cron_on = $terminal->cron['on'] ?? '06:00';
            $this->cron_off = $terminal->cron['off'] ?? '20:00';
            $this->has_active_cron = true;
        } else {
            $this->cron_on = '06:00';
            $this->cron_off = '20:00';
            $this->has_active_cron = false;
        }

        $this->showCronModal = true;
    }

    public function saveCron(): void
    {
        $terminal = MikyTerminal::findOrFail($this->cron_terminal_id);
        
        $success = app(MikyMeshService::class)->control($terminal, 'schedule_power', [
            'on_time' => $this->cron_on,
            'off_time' => $this->cron_off,
        ]);

        if ($success) {
            $terminal->update([
                'cron' => ['on' => $this->cron_on, 'off' => $this->cron_off]
            ]);
            app(MikyMeshService::class)->syncCluster();
            $this->showCronModal = false;
            session()->flash('status', "Rutina de energía programada en {$terminal->name}.");
        } else {
            session()->flash('error', "No se pudo enviar la rutina a la terminal.");
        }
    }

    public function deleteCron(): void
    {
        $terminal = MikyTerminal::findOrFail($this->cron_terminal_id);
        app(MikyMeshService::class)->control($terminal, 'clear_cron');
        $terminal->update(['cron' => null]);
        app(MikyMeshService::class)->syncCluster();
        $this->showCronModal = false;
        session()->flash('status', "Rutina de energía eliminada de {$terminal->name}.");
    }

    // --- DESPLIEGUES OTA & BRANDING ---
    public function fireOtaDeploy(): void
    {
        if (empty($this->cfg_ota_url) || ! str_contains($this->cfg_ota_url, 'raw.githubusercontent')) {
            session()->flash('error', 'Ingresá una URL Raw válida de GitHub.');
            return;
        }

        MikySetting::set('ota_url', $this->cfg_ota_url);
        $count = app(MikyMeshService::class)->deployOta($this->cfg_ota_url);
        app(MikyMeshService::class)->syncCluster();

        session()->flash('status', "Pipeline OTA iniciado en {$count} terminales activas.");
    }

    public function fireBrandingDeploy(): void
    {
        if (empty($this->cfg_logo_url)) {
            session()->flash('error', 'Ingresá la URL del logo del hospital.');
            return;
        }

        if (empty($this->cfg_ota_url) || ! str_contains($this->cfg_ota_url, 'agent.py')) {
            session()->flash('error', 'Se requiere la URL Raw de GitHub arriba para ubicar el ministerio.svg.');
            return;
        }

        MikySetting::set('logo_url', $this->cfg_logo_url);
        $count = app(MikyMeshService::class)->deployLogos($this->cfg_logo_url, $this->cfg_ota_url);
        app(MikyMeshService::class)->syncCluster();

        session()->flash('status', "Branding institucional desplegado en {$count} terminales activas.");
    }

    // --- CRUD Y CONFIG ---
    public function importFromMikyNode(): void
    {
        if (empty($this->import_node_ip)) {
            session()->flash('error', 'Ingresá la IP de una terminal.');
            return;
        }

        $service = app(MikyMeshService::class);
        $result = $service->pullFromNode($this->import_node_ip);

        if ($result['success']) {
            $this->loadSettings();
            $this->pollTerminals();
            $this->showImportModal = false;
            $this->import_node_ip = '';
            session()->flash('status', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function openTerminalModal(?int $id = null): void
    {
        $this->resetValidation();

        if ($id) {
            $terminal = MikyTerminal::findOrFail($id);
            $this->editing_terminal_id = $id;
            $this->term_name = $terminal->name;
            $this->term_ip = $terminal->ip;
            $this->term_mac = $terminal->mac ?? '';
            $this->term_sector = $terminal->sector;
            $this->term_resolution = $terminal->resolution ?? 'auto';
            $this->term_custom_buttons = $terminal->custom_buttons ?? [];
        } else {
            $this->editing_terminal_id = null;
            $this->term_name = '';
            $this->term_ip = '';
            $this->term_mac = '';
            $this->term_sector = $this->sectors[0] ?? 'General';
            $this->term_resolution = 'auto';
            $this->term_custom_buttons = [];
        }

        $this->showTerminalModal = true;
    }

    public function addCustomButton(): void
    {
        $this->term_custom_buttons[] = ['name' => '', 'url' => ''];
    }

    public function removeCustomButton(int $index): void
    {
        unset($this->term_custom_buttons[$index]);
        $this->term_custom_buttons = array_values($this->term_custom_buttons);
    }

    public function saveTerminal(): void
    {
        $this->validate();

        $data = [
            'name' => $this->term_name,
            'ip' => $this->term_ip,
            'mac' => $this->term_mac,
            'sector' => $this->term_sector,
            'resolution' => $this->term_resolution,
            'custom_buttons' => array_values(array_filter($this->term_custom_buttons, fn ($b) => ! empty($b['url']))),
        ];

        if ($this->editing_terminal_id) {
            $terminal = MikyTerminal::findOrFail($this->editing_terminal_id);
            $terminal->update($data);
            if ($terminal->resolution !== 'auto') {
                app(MikyMeshService::class)->control($terminal, 'set_resolution', ['resolution' => $terminal->resolution]);
            }
        } else {
            MikyTerminal::create($data);
        }

        app(MikyMeshService::class)->syncCluster();

        $this->showTerminalModal = false;
        $this->pollTerminals();
        session()->flash('status', 'Terminal guardada y replicada.');
    }

    public function deleteTerminal(int $id): void
    {
        MikyTerminal::findOrFail($id)->delete();
        app(MikyMeshService::class)->syncCluster();
        $this->pollTerminals();
        session()->flash('status', 'Terminal eliminada del clúster.');
    }

    public function saveConfig(): void
    {
        MikySetting::set('backend_token', $this->cfg_token);
        MikySetting::set('global_default_url', $this->cfg_global_url);
        MikySetting::set('maint_url', $this->cfg_maint_url);
        MikySetting::set('tg_token', $this->cfg_tg_token);
        MikySetting::set('tg_chat', $this->cfg_tg_chat);
        MikySetting::set('ota_url', $this->cfg_ota_url);
        MikySetting::set('logo_url', $this->cfg_logo_url);
        MikySetting::set('sectors', json_encode($this->sectors));

        app(MikyMeshService::class)->syncCluster();

        $this->showConfigModal = false;
        session()->flash('status', 'Configuración guardada y replicada en la red mesh.');
    }

    public function addSector(): void
    {
        $s = trim($this->new_sector);
        if (! empty($s) && ! in_array($s, $this->sectors)) {
            $this->sectors[] = $s;
            $this->new_sector = '';
        }
    }

    public function removeSector(string $sector): void
    {
        $this->sectors = array_values(array_diff($this->sectors, [$sector]));
    }

    public function render()
    {
        $query = MikyTerminal::where('is_active', true);

        if ($this->activeSector !== 'Todos') {
            $query->where('sector', $this->activeSector);
        }

        if (! empty($this->search)) {
            $s = "%{$this->search}%";
            $query->where(fn ($q) => $q->where('name', 'like', $s)->orWhere('ip', 'like', $s));
        }

        $terminals = $query->orderBy('name')->get();
        $onlineCount = collect($this->terminalStates)->filter(fn ($st) => $st['is_online'] ?? false)->count();

        return view('livewire.miky.miky-dashboard', [
            'terminals' => $terminals,
            'totalCount' => MikyTerminal::where('is_active', true)->count(),
            'onlineCount' => $onlineCount,
        ]);
    }
}