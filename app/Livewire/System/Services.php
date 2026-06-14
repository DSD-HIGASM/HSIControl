<?php

namespace App\Livewire\System;

use App\Models\Service;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.app')]
class Services extends Component
{
    public ?int $editId = null;

    public bool $confirmingDeletion = false;
    public ?int $serviceIdToDelete = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    protected function rules(): array
    {
        return [
            // Ignoramos unique para el mismo ID y para los registros inactivos (en la papelera)
            'name' => 'required|string|max:255|unique:services,name,' . $this->editId . ',id,deleted_at,NULL',
        ];
    }
    public function render()
    {
        return view('livewire.system.services', [
            'services' => Service::withTrashed()->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            $service = Service::withTrashed()->where('id', $this->editId)->first();
            if ($service) {
                $service->name = mb_strtoupper($this->name);
                $service->save();
            }
            $this->dispatch('notify', message: 'Servicio actualizado correctamente.', type: 'success');
        } else {
            Service::create(['name' => mb_strtoupper($this->name)]);
            $this->dispatch('notify', message: 'Servicio creado correctamente.', type: 'success');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $service = Service::withTrashed()->find($id);

        if ($service) {
            $this->editId = $service->id;
            $this->name = $service->name;
        }
    }

    public function restore(int $id): void
    {
        Service::onlyTrashed()->find($id)?->restore();
        $this->dispatch('notify', message: 'Servicio reactivado.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->serviceIdToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->serviceIdToDelete = null;
    }

    public function executeDelete(): void
    {
        if ($this->serviceIdToDelete) {
            $service = Service::find($this->serviceIdToDelete);
            
            if ($service) {
                $service->deleted_at = now();
                $service->save();
            }
            
            $this->dispatch('notify', message: 'Servicio desactivado correctamente.', type: 'error');
        }

        $this->cancelDelete();
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }
}
