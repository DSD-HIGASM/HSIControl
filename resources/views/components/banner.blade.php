@props(['text'])

<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
            {{ $text }}
        </h2>
        <div class="text-sm font-secondary text-brand-gray-custom">
            <div x-data="{ 
                fechaHora: '', 
                actualizar() {
                    const d = new Date();
                    const pad = (n) => String(n).padStart(2, '0');
                    this.fechaHora = `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
                }
            }" x-init="actualizar(); setInterval(() => actualizar(), 1000)">
                <span x-text="fechaHora"></span>
            </div>
        </div>
    </div>
</x-slot>