<div class="min-h-screen bg-gray-50 py-8" wire:poll.10s="pollTerminals"
    x-data="{
        sectorLive: false,
        streamInterval: parseInt(localStorage.getItem('miky_stream_speed') || '2000'),
        setStreamInterval(val) {
            this.streamInterval = parseInt(val);
            localStorage.setItem('miky_stream_speed', this.streamInterval);
        },
        toggleSectorLive() {
            this.sectorLive = !this.sectorLive;
            $dispatch('toggle-sector-live', this.sectorLive);
        }
    }">
    <div class="max-w-[94rem] mx-auto px-4 sm:px-6 lg:px-8">

        <!-- MENSAJES FLASH -->
        @if (session()->has('status'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold font-secondary flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-600 shrink-0" />
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-brand-pink text-xs font-bold font-secondary flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <x-heroicon-s-x-circle class="w-5 h-5 text-brand-pink shrink-0" />
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif

        <!-- CABECERA -->
        <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-5 bg-white rounded-xl shadow-xs border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-brand-cyan/10 text-brand-cyan-dark rounded-xl">
                    <x-heroicon-o-tv class="w-7 h-7 text-brand-cyan" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 font-secondary flex items-center gap-2">
                        Control de Pantallas (Miky7)
                    </h2>
                    <p class="text-xs text-gray-500 font-secondary mt-0.5">
                        Centro de comando y monitoreo en tiempo real de terminales y llamadores de guardia.
                    </p>
                </div>
            </div>

            <!-- CONTADOR Y BOTONES GLOBALES -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-secondary font-bold">
                    <span class="w-2.5 h-2.5 rounded-full {{ $onlineCount > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300' }}"></span>
                    <span class="text-gray-600">Online: <strong class="text-emerald-700">{{ $onlineCount }}</strong> / {{ $totalCount }}</span>
                </div>

                <!-- ACCIONES MASIVAS -->
                <div class="inline-flex rounded-lg shadow-2xs border border-gray-200 bg-white p-1 gap-1" title="Acciones Masivas">
                    <button wire:click="launchGlobalUrl" title="Lanzar URL Global a todas las pantallas"
                        wire:confirm="¿Deseas enviar la URL Global a todas las pantallas activas?"
                        class="p-2 text-brand-cyan hover:bg-cyan-50 rounded-md transition-colors">
                        <x-heroicon-o-globe-alt class="w-4 h-4" />
                    </button>
                    <button wire:click="globalControl('refresh')" title="Recargar (F5) en todas las pantallas"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                    </button>
                    <button wire:click="globalControl('clear_cache')" title="Limpiar caché en todas las pantallas"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                    <button wire:click="globalControl('reboot')" title="Reiniciar todas las terminales"
                        wire:confirm="¿Seguro que deseas reiniciar el hardware de TODAS las terminales conectadas?"
                        class="p-2 text-brand-pink hover:bg-rose-50 rounded-md transition-colors">
                        <x-heroicon-o-power class="w-4 h-4" />
                    </button>
                </div>

                <button wire:click="$set('showImportModal', true)"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg transition-colors font-secondary shadow-2xs">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-brand-cyan" />
                    Importar Nodo
                </button>

                <button wire:click="$set('showConfigModal', true)"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg transition-colors font-secondary shadow-2xs">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-gray-500" />
                    Configuración
                </button>

                <button wire:click="openTerminalModal"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-cyan hover:bg-cyan-600 text-white text-xs font-bold rounded-lg transition-colors font-secondary shadow-2xs">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Nueva Terminal
                </button>
            </div>
        </div>

        <!-- SECTORES, BUSCADOR Y CONTROLES LIVE -->
        <div class="mb-6 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            
            <!-- Pestañas de Sectores -->
            <div class="flex items-center gap-1 overflow-x-auto hide-scrollbar bg-white p-1.5 rounded-xl border border-gray-200 shadow-2xs">
                <button wire:click="$set('activeSector', 'Todos')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold font-secondary transition-all whitespace-nowrap {{ $activeSector === 'Todos' ? 'bg-brand-cyan text-white shadow-2xs' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                    Todos ({{ $totalCount }})
                </button>
                @foreach($sectors as $sec)
                    <button wire:click="$set('activeSector', '{{ $sec }}')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold font-secondary transition-all whitespace-nowrap {{ $activeSector === $sec ? 'bg-brand-cyan text-white shadow-2xs' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        {{ $sec }}
                    </button>
                @endforeach
            </div>

            <!-- Controles Live (Slider + Toggle Live + Buscador + Vista) -->
            <div class="flex flex-wrap items-center gap-3">
                
                <!-- CONTROL DESLIZABLE ULTRA RÁPIDO (MODO VIDEO) -->
                <div class="flex items-center gap-2.5 bg-white border border-gray-300 rounded-lg px-3 py-1.5 shadow-2xs text-xs font-secondary">
                    <span class="text-[10px] font-bold text-gray-500 uppercase flex items-center gap-1 shrink-0">
                        <span class="transition-colors" :class="streamInterval <= 300 ? 'text-red-500 animate-bounce' : 'text-amber-500'">
                            <x-heroicon-o-bolt class="w-3.5 h-3.5" />
                        </span>
                        Refresco:
                    </span>
                    
                    <input type="range" 
                        min="50" 
                        max="10000" 
                        step="50" 
                        x-model="streamInterval" 
                        @input="setStreamInterval($event.target.value)"
                        class="w-28 sm:w-36 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer focus:outline-none"
                        :class="streamInterval <= 300 ? 'accent-red-600' : 'accent-brand-cyan'"
                        title="Ajustar velocidad (bajar a la izquierda para modo video)">

                    <span class="font-mono font-black text-[11px] px-2 py-0.5 rounded w-16 text-center shrink-0 transition-colors"
                        :class="streamInterval <= 300 
                            ? 'bg-red-50 text-red-600 border border-red-200 animate-pulse' 
                            : 'bg-brand-cyan/10 text-brand-cyan-dark'"
                        x-text="streamInterval < 1000 ? streamInterval + 'ms' : (streamInterval / 1000).toFixed(1) + 's'">
                    </span>
                </div>

                <!-- BOTÓN GLOBAL LIVE PARA EL SECTOR -->
                <button @click="toggleSectorLive()"
                    type="button"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold font-secondary transition-all shadow-2xs"
                    :class="sectorLive ? 'bg-red-600 hover:bg-red-700 text-white animate-pulse' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300'">
                    <span class="w-2 h-2 rounded-full" :class="sectorLive ? 'bg-white' : 'bg-red-500'"></span>
                    <span x-text="sectorLive ? 'Detener Live ({{ $terminals->count() }})' : '● Transmisión en Vivo ({{ $terminals->count() }})'"></span>
                </button>

                <div class="relative w-56">
                    <input type="text" wire:model.live.debounce.250ms="search" placeholder="Buscar terminal..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border-gray-300 shadow-2xs focus:border-brand-cyan focus:ring-0 font-secondary">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-2.5 top-2 pointer-events-none" />
                </div>

                <div class="flex bg-white rounded-lg border border-gray-200 p-1 shadow-2xs">
                    <button wire:click="$set('view', 'grid')" title="Vista Cuadrícula" class="p-1 rounded {{ $view === 'grid' ? 'bg-gray-100 text-brand-cyan' : 'text-gray-400' }}">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                    </button>
                    <button wire:click="$set('view', 'list')" title="Vista Lista" class="p-1 rounded {{ $view === 'list' ? 'bg-gray-100 text-brand-cyan' : 'text-gray-400' }}">
                        <x-heroicon-o-bars-3 class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- VISTA EN CUADRÍCULA (16:9 AGRANDADOS) -->
        @if($view === 'grid')
            <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6">
                @forelse($terminals as $term)
                    @php
                        $state = $terminalStates[$term->id] ?? ['is_online' => false, 'cpu' => 0, 'vol' => '0', 'url' => '', 'kiosk_lock' => true, 'leader' => null];
                        $isOnline = $state['is_online'];
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-xs hover:shadow-md transition-shadow flex flex-col group relative">
                        
                        <!-- Header de Tarjeta -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/70 flex items-center justify-between">
                            <div class="flex items-center gap-2.5 truncate">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $isOnline ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-gray-300' }}"></span>
                                <div class="truncate">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="text-xs font-bold text-gray-900 truncate font-secondary uppercase">{{ $term->name }}</h4>
                                        @if($state['leader'] && $state['leader'] === $term->ip)
                                            <span class="text-[10px] text-amber-500 font-bold" title="Nodo Capitán">👑</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-mono">{{ $term->ip }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white border border-gray-200 text-amber-600 font-mono" title="Volumen">
                                    🔊 {{ $state['vol'] }}%
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white border border-gray-200 font-mono {{ $state['cpu'] > 80 ? 'text-brand-pink font-extrabold' : 'text-brand-cyan-dark' }}" title="Carga CPU">
                                    ⚡ {{ $state['cpu'] }}%
                                </span>
                                <button wire:click="openTerminalModal({{ $term->id }})" class="text-gray-400 hover:text-gray-700 p-1 rounded-md hover:bg-gray-100 transition-colors" title="Editar Terminal">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Pantalla 16:9 con Frecuencia Dinámica -->
                        <div class="relative bg-slate-950 aspect-video w-full flex items-center justify-center cursor-pointer group/screen overflow-hidden"
                            wire:click="openScreenshotModal({{ $term->id }})"
                            x-data="{
                                imgSrc: '{{ route('miky.screenshot', $term->id) }}',
                                loading: true,
                                isLive: false,
                                timer: null,
                                reload() {
                                    this.imgSrc = '{{ route('miky.screenshot', $term->id) }}?t=' + Date.now();
                                },
                                toggleLive() {
                                    this.isLive = !this.isLive;
                                    if (this.isLive) {
                                        this.reload();
                                    } else {
                                        clearTimeout(this.timer);
                                    }
                                },
                                handleNext() {
                                    this.loading = false;
                                    if (this.isLive) {
                                        this.timer = setTimeout(() => this.reload(), streamInterval);
                                    }
                                }
                            }"
                            x-on:toggle-sector-live.window="isLive = $event.detail; if(isLive) reload(); else { clearTimeout(timer); }">

                            @if($isOnline)
                                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-slate-900/80 z-10">
                                    <x-heroicon-o-arrow-path class="w-7 h-7 text-brand-cyan animate-spin" />
                                </div>

                                <img :src="imgSrc"
                                    x-on:load="handleNext()"
                                    x-on:error="handleNext()"
                                    class="max-w-full max-h-full object-contain opacity-95 transition-opacity group-hover/screen:opacity-100">

                                <button @click.stop="toggleLive()"
                                    type="button"
                                    class="absolute top-2.5 left-2.5 z-20 px-2 py-0.5 rounded text-[10px] font-bold font-mono transition-all flex items-center gap-1.5"
                                    :class="isLive ? 'bg-red-600 text-white shadow-md animate-pulse' : 'bg-black/60 text-gray-300 hover:bg-black/90 hover:text-white'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="isLive ? 'bg-white' : 'bg-red-500'"></span>
                                    <span x-text="isLive ? 'TRANSMITIENDO' : 'EN VIVO'"></span>
                                </button>

                                <button @click.stop="reload()"
                                    type="button"
                                    class="absolute top-2.5 right-2.5 bg-black/60 hover:bg-black p-1.5 rounded-md text-white opacity-0 group-hover/screen:opacity-100 transition-opacity z-20"
                                    title="Capturar fotograma manual">
                                    <x-heroicon-o-camera class="w-4 h-4" />
                                </button>
                            @else
                                <div class="text-center p-6">
                                    <x-heroicon-o-tv class="w-10 h-10 text-gray-700 mx-auto mb-2" />
                                    <span class="text-xs text-gray-500 font-mono block">Terminal Fuera de Línea</span>
                                    @if($term->mac)
                                        <button wire:click.stop="triggerWakeOnLan({{ $term->id }})"
                                            class="mt-2.5 px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold font-secondary rounded-md shadow-2xs">
                                            Despertar (WOL)
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <span class="absolute bottom-2 left-2 text-[10px] text-white/80 font-mono bg-black/75 px-2.5 py-1 rounded max-w-[85%] truncate pointer-events-none z-20">
                                {{ $state['url'] ?: 'Esperando enlace...' }}
                            </span>
                        </div>

                        <!-- Botones Rápidos de URL -->
                        @if(!empty($term->custom_buttons))
                            <div class="px-3.5 pt-2.5 pb-1 flex flex-wrap gap-1.5 border-b border-gray-100 bg-gray-50/40">
                                @foreach($term->custom_buttons as $btn)
                                    <button wire:click="setStartupUrl({{ $term->id }}, '{{ $btn['url'] }}')"
                                        class="text-[11px] font-bold font-secondary px-2.5 py-1 rounded-md bg-white border border-gray-200 hover:border-brand-cyan hover:text-brand-cyan-dark text-gray-700 transition-colors shadow-2xs truncate max-w-[200px]"
                                        title="{{ $btn['url'] }}">
                                        ▶ {{ $btn['name'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Botonera de Control -->
                        <div class="p-3.5 bg-white space-y-2 mt-auto">
                            <div class="flex items-center gap-1.5">
                                <div class="inline-flex rounded-md border border-gray-200 bg-gray-50 p-0.5">
                                    <button wire:click="executeCommand({{ $term->id }}, 'vol_down')" class="px-2 py-1 text-gray-600 hover:text-brand-cyan font-bold text-xs" title="Bajar volumen">-</button>
                                    <button wire:click="executeCommand({{ $term->id }}, 'vol_up')" class="px-2 py-1 text-gray-600 hover:text-brand-cyan font-bold text-xs border-l border-gray-200" title="Subir volumen">+</button>
                                </div>

                                <button wire:click="executeCommand({{ $term->id }}, 'refresh')" class="flex-1 py-1.5 px-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-md font-secondary flex items-center justify-center gap-1" title="Refrescar pantalla (F5)">
                                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-gray-500" /> F5
                                </button>

                                <button wire:click="setStartupUrl({{ $term->id }}, '{{ $cfg_global_url }}')" class="p-1.5 bg-cyan-50 hover:bg-cyan-100 text-brand-cyan-dark border border-cyan-200 rounded-md" title="Lanzar URL Global">
                                    <x-heroicon-o-globe-alt class="w-4 h-4" />
                                </button>

                                <button wire:click="executeCommand({{ $term->id }}, 'reset_hdmi')" class="p-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-md" title="Restablecer Resolución / HDMI">
                                    <x-heroicon-o-arrows-pointing-out class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button wire:click="openCronModal({{ $term->id }})"
                                    class="py-1 px-2.5 rounded-md border text-[11px] font-bold font-secondary flex items-center gap-1 transition-colors {{ !empty($term->cron) ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-gray-50 text-gray-600 border-gray-200 hover:text-purple-600' }}"
                                    title="Programador de Energía Automático">
                                    <x-heroicon-o-clock class="w-3.5 h-3.5 text-purple-600" />
                                    {{ !empty($term->cron) ? $term->cron['on'] . ' - ' . $term->cron['off'] : 'CRON' }}
                                </button>

                                <button wire:click="setStartupUrl({{ $term->id }}, '{{ $cfg_maint_url }}')"
                                    class="flex-1 py-1 px-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-[11px] font-bold rounded-md font-secondary flex items-center justify-center gap-1"
                                    title="Pantalla de Mantenimiento">
                                    <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 text-amber-600" /> Mant.
                                </button>

                                <button wire:click="toggleKioskLock({{ $term->id }}, {{ !($state['kiosk_lock'] ?? true) ? 'true' : 'false' }})"
                                    class="p-1.5 rounded-md border text-[11px] {{ ($state['kiosk_lock'] ?? true) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-brand-pink border-rose-200' }}"
                                    title="{{ ($state['kiosk_lock'] ?? true) ? 'Watchdog de Kiosko Activo' : 'Watchdog Pausado' }}">
                                    @if($state['kiosk_lock'] ?? true)
                                        <x-heroicon-s-lock-closed class="w-4 h-4" />
                                    @else
                                        <x-heroicon-s-lock-open class="w-4 h-4" />
                                    @endif
                                </button>

                                <button wire:click="executeCommand({{ $term->id }}, 'reboot')"
                                    wire:confirm="¿Seguro que deseas reiniciar físicamente la terminal {{ $term->name }}?"
                                    class="p-1.5 bg-rose-50 hover:bg-rose-100 text-brand-pink border border-rose-200 rounded-md" title="Reiniciar Equipo (sudo reboot)">
                                    <x-heroicon-o-power class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 px-6 text-center bg-white rounded-xl border border-gray-200 shadow-2xs max-w-xl mx-auto my-8">
                        <x-heroicon-o-tv class="w-12 h-12 mx-auto text-brand-cyan mb-3" />
                        <h3 class="text-base font-bold text-gray-900 font-secondary uppercase">No hay terminales registradas</h3>
                        <p class="text-xs text-gray-500 font-secondary mt-1 mb-5">
                            Podés importar la base de datos de Miky7 desde una terminal encendida o cargarlas manualmente.
                        </p>

                        <div class="flex items-center justify-center gap-3">
                            <button wire:click="$set('showImportModal', true)"
                                class="px-4 py-2 bg-brand-cyan hover:bg-cyan-600 text-white font-bold text-xs rounded-lg shadow-2xs font-secondary flex items-center gap-1.5">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                Importar desde IP de Nodo
                            </button>
                            <button wire:click="openTerminalModal"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-lg font-secondary">
                                + Crear Manualmente
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <!-- VISTA EN LISTA -->
            <div class="bg-white rounded-xl shadow-2xs border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-xs font-secondary">
                    <thead class="bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Estado</th>
                            <th class="px-5 py-3 text-left">Terminal</th>
                            <th class="px-5 py-3 text-left">Sector</th>
                            <th class="px-5 py-3 text-left">IP / MAC</th>
                            <th class="px-5 py-3 text-left">URL Actual</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($terminals as $term)
                            @php $st = $terminalStates[$term->id] ?? ['is_online' => false, 'url' => '']; @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $st['is_online'] ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                        <span class="w-2 h-2 rounded-full {{ $st['is_online'] ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                        {{ $st['is_online'] ? 'ONLINE' : 'OFFLINE' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-bold text-gray-900 uppercase">{{ $term->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $term->sector }}</td>
                                <td class="px-5 py-3 font-mono text-[11px] text-gray-600">
                                    <div>{{ $term->ip }}</div>
                                    <div class="text-[9px] text-gray-400">{{ $term->mac ?: '—' }}</div>
                                </td>
                                <td class="px-5 py-3 font-mono text-[10px] text-gray-400 truncate max-w-xs">{{ $st['url'] ?: '—' }}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="openCronModal({{ $term->id }})" class="p-1 text-purple-600 hover:text-purple-800" title="CRON"><x-heroicon-o-clock class="w-4 h-4" /></button>
                                        <button wire:click="executeCommand({{ $term->id }}, 'refresh')" class="p-1 text-gray-600 hover:text-brand-cyan" title="Refrescar (F5)"><x-heroicon-o-arrow-path class="w-4 h-4" /></button>
                                        <button wire:click="setStartupUrl({{ $term->id }}, '{{ $cfg_global_url }}')" class="p-1 text-brand-cyan hover:text-brand-cyan-dark" title="URL Global"><x-heroicon-o-globe-alt class="w-4 h-4" /></button>
                                        <button wire:click="openTerminalModal({{ $term->id }})" class="p-1 text-gray-500 hover:text-gray-800" title="Editar"><x-heroicon-o-pencil-square class="w-4 h-4" /></button>
                                        <button wire:click="deleteTerminal({{ $term->id }})" wire:confirm="¿Eliminar esta terminal?" class="p-1 text-gray-400 hover:text-brand-pink" title="Borrar"><x-heroicon-o-trash class="w-4 h-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-400 font-secondary">
                                    No se encontraron terminales registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

    </div>

    <!-- MODAL LIVE SCREENSHOT EXPANDIDO -->
    @if($showScreenshotModal && $modalTerminalId)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <div class="fixed inset-0 bg-gray-900/85 backdrop-blur-sm" wire:click="$set('showScreenshotModal', false)"></div>
                <div class="relative w-full max-w-5xl bg-black rounded-2xl overflow-hidden shadow-2xl p-4 border border-white/10 z-10"
                    x-data="{
                        imgSrc: '{{ route('miky.screenshot', $modalTerminalId) }}?t=' + Date.now(),
                        fpsTimer: null,
                        streamActive: true,
                        nextFrame() {
                            if (!this.streamActive) return;
                            this.fpsTimer = setTimeout(() => {
                                this.imgSrc = '{{ route('miky.screenshot', $modalTerminalId) }}?t=' + Date.now();
                            }, streamInterval);
                        },
                        toggleStream() {
                            this.streamActive = !this.streamActive;
                            if (this.streamActive) this.nextFrame();
                            else clearTimeout(this.fpsTimer);
                        }
                    }">
                    
                    <div class="flex justify-between items-center mb-3 px-2">
                        <div class="flex items-center gap-3">
                            <h3 class="text-white font-bold text-sm font-secondary uppercase">{{ $modalTerminalName }}</h3>
                            <button @click="toggleStream()"
                                type="button"
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono flex items-center gap-1.5 transition-colors"
                                :class="streamActive ? 'bg-red-500/20 text-red-400 border border-red-500/40 animate-pulse' : 'bg-gray-800 text-gray-400 border border-gray-700'">
                                <span class="w-2 h-2 rounded-full" :class="streamActive ? 'bg-red-500' : 'bg-gray-500'"></span>
                                <span x-text="streamActive ? 'STREAMING EN VIVO' : 'PAUSADO'"></span>
                            </button>
                        </div>
                        <button wire:click="$set('showScreenshotModal', false)" class="text-gray-400 hover:text-white">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="h-[75vh] flex items-center justify-center bg-zinc-950 rounded-lg overflow-hidden border border-white/5 relative">
                        <img :src="imgSrc"
                            x-on:load="nextFrame()"
                            x-on:error="nextFrame()"
                            class="max-w-full max-h-full object-contain rounded-lg">
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL GESTOR DE ENERGÍA (CRON) -->
    @if($showCronModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm" wire:click="$set('showCronModal', false)"></div>
                <div class="relative w-full max-w-sm bg-white rounded-xl shadow-2xl overflow-hidden border-t-4 border-purple-600 z-10">
                    <form wire:submit="saveCron">
                        <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900 font-secondary uppercase flex items-center gap-2">
                                <x-heroicon-o-clock class="w-5 h-5 text-purple-600" />
                                Gestor de Energía (CRON)
                            </h3>
                            <button type="button" wire:click="$set('showCronModal', false)" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="p-5 space-y-4 text-xs font-secondary">
                            <p class="text-gray-600 leading-relaxed">
                                Horario para apagar y encender automáticamente la pantalla de <strong class="text-gray-900 font-bold uppercase">{{ $cron_pc_name }}</strong>.
                            </p>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Encendido (DPMS On)</label>
                                    <input type="time" wire:model="cron_on" class="w-full rounded-md border-gray-300 font-mono text-sm focus:border-purple-600 focus:ring-0">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Apagado (DPMS Off)</label>
                                    <input type="time" wire:model="cron_off" class="w-full rounded-md border-gray-300 font-mono text-sm focus:border-purple-600 focus:ring-0">
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                            @if($has_active_cron)
                                <button type="button" wire:click="deleteCron" class="text-brand-pink hover:underline text-xs font-bold font-secondary">
                                    Eliminar Rutina
                                </button>
                            @else
                                <div></div>
                            @endif

                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('showCronModal', false)" class="px-3 py-1.5 bg-white border rounded-md text-xs font-bold text-gray-700 font-secondary">Cancelar</button>
                                <button type="submit" class="px-4 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-xs font-bold shadow-2xs font-secondary">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE CONFIGURACIÓN GLOBAL -->
    @if($showConfigModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm" wire:click="$set('showConfigModal', false)"></div>
                <div class="relative w-full max-w-3xl bg-white rounded-xl shadow-2xl overflow-hidden border-t-4 border-brand-cyan z-10">
                    <form wire:submit="saveConfig">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900 font-secondary uppercase flex items-center gap-2">
                                <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-brand-cyan" /> Parámetros y Despliegues del Clúster Miky7
                            </h3>
                            <button type="button" wire:click="$set('showConfigModal', false)" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="p-6 space-y-6 text-xs font-secondary max-h-[72vh] overflow-y-auto">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Bearer Token (BACKEND_TOKEN)</label>
                                    <input type="password" wire:model="cfg_token" class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0" placeholder="Clave de seguridad">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">URL de Mantenimiento</label>
                                    <input type="text" wire:model="cfg_maint_url" class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0" placeholder="local o https://...">
                                </div>
                                <div class="col-span-2">
                                    <label class="block font-bold text-gray-700 uppercase mb-1">URL Global Predeterminada</label>
                                    <input type="text" wire:model="cfg_global_url" class="w-full rounded-md border-gray-300 font-mono text-xs text-brand-cyan-dark focus:border-brand-cyan focus:ring-0" placeholder="https://shc.ms.gba.gov.ar/...">
                                </div>
                            </div>

                            <div class="p-4 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-indigo-900 uppercase tracking-wide flex items-center gap-1.5">
                                        <x-heroicon-o-arrow-up-on-square-stack class="w-4 h-4 text-indigo-600" />
                                        Despliegue OTA (agent.py)
                                    </h4>
                                    <button type="button" wire:click="fireOtaDeploy" wire:confirm="¿Iniciar pipeline de actualización OTA en TODAS las terminales conectadas?"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-[11px] font-bold uppercase transition-colors shadow-2xs">
                                        Deploy OTA Masivo
                                    </button>
                                </div>
                                <input type="text" wire:model="cfg_ota_url" placeholder="https://raw.githubusercontent.com/.../agent.py"
                                    class="w-full rounded-md border-indigo-200 font-mono text-xs text-indigo-700 focus:border-indigo-500 focus:ring-0">
                                <p class="text-[10px] text-indigo-600/80">Descarga la nueva versión en segundo plano y reinicia las mini PCs de forma orquestada.</p>
                            </div>

                            <div class="p-4 bg-pink-50/70 border border-pink-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-pink-900 uppercase tracking-wide flex items-center gap-1.5">
                                        <x-heroicon-o-photo class="w-4 h-4 text-brand-pink" />
                                        Despliegue de Branding Institucional
                                    </h4>
                                    <button type="button" wire:click="fireBrandingDeploy" wire:confirm="¿Inyectar logos en todas las pantallas?"
                                        class="px-3 py-1.5 bg-brand-pink hover:bg-pink-700 text-white rounded-md text-[11px] font-bold uppercase transition-colors shadow-2xs">
                                        Actualizar Logos
                                    </button>
                                </div>
                                <input type="text" wire:model="cfg_logo_url" placeholder="https://mi-hospital.com/logo_hospital.png"
                                    class="w-full rounded-md border-pink-200 font-mono text-xs text-pink-700 focus:border-brand-pink focus:ring-0">
                                <p class="text-[10px] text-pink-600/80">Inyecta el logo hospitalario y sincroniza el ministerio.svg utilizando la ruta base configurada arriba.</p>
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <label class="block font-bold text-gray-700 uppercase mb-2">Sectores de Hospital (Nombres Libres)</label>
                                <div class="flex gap-2 mb-3">
                                    <input type="text" wire:model="new_sector" placeholder="Nuevo sector (ej: Triage)..." class="flex-1 rounded-md border-gray-300 text-xs focus:border-brand-cyan focus:ring-0">
                                    <button type="button" wire:click="addSector" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 font-bold rounded-md text-xs">Añadir</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($sectors as $sec)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $sec }}
                                            <button type="button" wire:click="removeSector('{{ $sec }}')" class="text-gray-400 hover:text-brand-pink">✕</button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <label class="block font-bold text-gray-700 uppercase mb-2">Alertas de Caída de Red (Telegram)</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <input type="password" wire:model="cfg_tg_token" placeholder="Bot Token" class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0">
                                    </div>
                                    <div>
                                        <input type="text" wire:model="cfg_tg_chat" placeholder="Chat ID" class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showConfigModal', false)" class="px-4 py-2 bg-white border rounded-md text-xs font-bold text-gray-700 font-secondary">Cerrar</button>
                            <button type="submit" class="px-5 py-2 bg-brand-cyan hover:bg-cyan-600 text-white rounded-md text-xs font-bold shadow-2xs font-secondary">Guardar y Replicar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE ALTA / EDICIÓN TERMINAL -->
    @if($showTerminalModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm" wire:click="$set('showTerminalModal', false)"></div>
                <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl overflow-hidden border-t-4 border-brand-cyan z-10">
                    <form wire:submit="saveTerminal">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900 font-secondary uppercase">
                                {{ $editing_terminal_id ? 'Editar Terminal' : 'Nueva Terminal' }}
                            </h3>
                            <button type="button" wire:click="$set('showTerminalModal', false)" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="p-6 space-y-4 text-xs font-secondary">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Nombre Identificatorio *</label>
                                    <input type="text" wire:model="term_name" placeholder="Ej: Guardia Adultos - Box 1"
                                        class="w-full rounded-md border-gray-300 text-xs focus:border-brand-cyan focus:ring-0">
                                    @error('term_name') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Dirección IP LAN *</label>
                                    <input type="text" wire:model="term_ip" placeholder="192.168.1.50"
                                        class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0">
                                    @error('term_ip') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Sector *</label>
                                    <select wire:model="term_sector" class="w-full rounded-md border-gray-300 text-xs focus:border-brand-cyan focus:ring-0">
                                        @foreach($sectors as $sec)
                                            <option value="{{ $sec }}">{{ $sec }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">Resolución</label>
                                    <select wire:model="term_resolution" class="w-full rounded-md border-gray-300 text-xs font-mono focus:border-brand-cyan focus:ring-0">
                                        <option value="auto">Automática</option>
                                        <option value="1920x1080">1920x1080</option>
                                        <option value="1366x768">1366x768</option>
                                        <option value="1280x720">1280x720</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1">MAC (WOL)</label>
                                    <input type="text" wire:model="term_mac" placeholder="00:11:22:33:44:55"
                                        class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0">
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-gray-700 uppercase tracking-wide">Botones Rápidos de URL</span>
                                    <button type="button" wire:click="addCustomButton" class="text-brand-cyan hover:underline font-bold">+ Agregar Botón</button>
                                </div>
                                <div class="space-y-2 max-h-40 overflow-y-auto">
                                    @foreach($term_custom_buttons as $idx => $btn)
                                        <div class="flex items-center gap-2">
                                            <input type="text" wire:model="term_custom_buttons.{{ $idx }}.name" placeholder="Nombre (ej: Triage)" class="w-1/3 rounded-md border-gray-300 text-xs focus:border-brand-cyan focus:ring-0">
                                            <input type="text" wire:model="term_custom_buttons.{{ $idx }}.url" placeholder="https://..." class="flex-1 rounded-md border-gray-300 font-mono text-xs text-brand-cyan-dark focus:border-brand-cyan focus:ring-0">
                                            <button type="button" wire:click="removeCustomButton({{ $idx }})" class="text-gray-400 hover:text-brand-pink p-1"><x-heroicon-o-trash class="w-4 h-4" /></button>
                                        </div>
                                    @endforeach
                                    @if(empty($term_custom_buttons))
                                        <p class="text-gray-400 italic text-[11px]">Sin botones rápidos configurados.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showTerminalModal', false)" class="px-4 py-2 bg-white border rounded-md text-xs font-bold text-gray-700 font-secondary">Cancelar</button>
                            <button type="submit" class="px-5 py-2 bg-brand-cyan hover:bg-cyan-600 text-white rounded-md text-xs font-bold shadow-2xs font-secondary">Guardar Terminal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL IMPORTAR NODO -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm" wire:click="$set('showImportModal', false)"></div>
                <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden border-t-4 border-brand-cyan z-10">
                    <form wire:submit="importFromMikyNode">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900 font-secondary uppercase flex items-center gap-2">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-brand-cyan" />
                                Traer Base de Datos Miky
                            </h3>
                            <button type="button" wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="p-6 space-y-4 text-xs font-secondary">
                            <p class="text-gray-600 leading-relaxed">
                                Ingresá la IP local de cualquier mini PC encendida. Laravel volcará todas las terminales, sectores y configuraciones a MySQL vía <code class="font-mono bg-gray-100 px-1 py-0.5 rounded text-brand-cyan-dark">/sync</code>.
                            </p>

                            <div>
                                <label class="block font-bold text-gray-700 uppercase mb-1">IP de una Terminal Activa *</label>
                                <input type="text" wire:model="import_node_ip" placeholder="Ej: 192.168.1.50"
                                    class="w-full rounded-md border-gray-300 font-mono text-xs focus:border-brand-cyan focus:ring-0">
                                @error('import_node_ip') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800">
                                <strong>Nota:</strong> Verificá que en <em>Configuración</em> tengas cargado el mismo <code>BACKEND_TOKEN</code> que tiene configurado esa terminal.
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showImportModal', false)" class="px-4 py-2 bg-white border rounded-md text-xs font-bold text-gray-700 font-secondary">Cancelar</button>
                            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 bg-brand-cyan hover:bg-cyan-600 text-white rounded-md text-xs font-bold shadow-2xs font-secondary disabled:opacity-50">
                                <span wire:loading.remove wire:target="importFromMikyNode">Sincronizar e Importar</span>
                                <span wire:loading wire:target="importFromMikyNode">Conectando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>