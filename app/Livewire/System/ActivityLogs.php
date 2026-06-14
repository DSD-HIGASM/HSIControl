<?php

namespace App\Livewire\System;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app')]
class ActivityLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $event = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    // Variables para el modal de detalles
    public ?Activity $selectedLog = null;

    public bool $showingDetails = false;

    public function updating($property): void
    {
        if (in_array($property, ['search', 'event', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Activity::with(['causer.agent'])->latest();

        if (! empty($this->event)) {
            $query->where('event', $this->event);
        }

        if (! empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (! empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('subject_type', 'like', '%'.$this->search.'%');
            });
        }

        return view('livewire.system.activity-logs', [
            'logs' => $query->paginate(15),
        ]);
    }

    // Método para abrir el modal con el detalle de los cambios
    public function showDetails(int $id): void
    {
        $this->selectedLog = Activity::with(['causer.agent'])->find($id);
        $this->showingDetails = true;
    }

    // Método para cerrar el modal
    public function closeDetails(): void
    {
        $this->showingDetails = false;
        // Limpiamos el objeto por un tema de memoria y seguridad
        $this->selectedLog = null;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'event', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function revertLog(int $logId): void
    {
        $log = Activity::find($logId);

        // Validamos que exista, que sea un update y que el modelo no haya sido borrado físicamente
        if ($log && $log->event === 'updated' && $log->subject) {
            
            // Leemos la columna tal como lo hacemos en la vista
            $rawData = $log->attribute_changes ?? $log->properties ?? [];
            $changes = is_string($rawData) ? collect(json_decode($rawData, true)) : collect($rawData);
            
            $oldValues = $changes->get('old', []);

            if (!empty($oldValues)) {
                // Restauramos el modelo inyectando los valores viejos
                // Al hacer update(), el trait de Spatie detectará automáticamente 
                // esto como un cambio y creará un nuevo log registrando esta "reversión"
                $log->subject->update($oldValues);
                
                $this->dispatch('notify', message: 'Se han restaurado los valores anteriores correctamente.', type: 'success');
                $this->closeDetails();
            } else {
                $this->dispatch('notify', message: 'No hay valores anteriores legibles para restaurar.', type: 'error');
            }
        } else {
            $this->dispatch('notify', message: 'Este evento no se puede revertir o el registro original ya no existe.', type: 'error');
        }
    }
}
