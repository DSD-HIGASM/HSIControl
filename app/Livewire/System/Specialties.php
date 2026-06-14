<?php

namespace App\Livewire\System;

use App\Models\Speciality;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.app')]
class Specialties extends Component
{
    public ?int $editId = null;
    
    public bool $confirmingDeletion = false;
    public ?int $SpecialityIdToDelete = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    protected function rules(): array
    {
        return [
            // Ignoramos unique para el mismo ID y para los registros inactivos (en la papelera)
            'name' => 'required|string|max:255|unique:specialties,name,' . $this->editId . ',id,deleted_at,NULL',
        ];
    }

    public function render()
    {
        return view('livewire.system.specialties', [
            'specialties' => Speciality::withTrashed()->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            $Speciality = Speciality::withTrashed()->where('id', $this->editId)->first();
            if ($Speciality) {
                $Speciality->name = mb_strtoupper($this->name);
                $Speciality->save();
            }
            $this->dispatch('notify', message: 'Especialidad actualizada correctamente.', type: 'success');
        } else {
            Speciality::create(['name' => mb_strtoupper($this->name)]);
            $this->dispatch('notify', message: 'Especialidad creada correctamente.', type: 'success');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $Speciality = Speciality::withTrashed()->find($id);
        
        if ($Speciality) {
            $this->editId = $Speciality->id;
            $this->name = $Speciality->name;
        }
    }

    public function restore(int $id): void
    {
        Speciality::onlyTrashed()->find($id)?->restore();
        $this->dispatch('notify', message: 'Especialidad reactivada.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->SpecialityIdToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->SpecialityIdToDelete = null;
    }

    public function executeDelete(): void
    {
        if ($this->SpecialityIdToDelete) {
            $Speciality = Speciality::find($this->SpecialityIdToDelete);
            
            if ($Speciality) {
                // Borrado lógico con save() para evitar el bloqueo del motor SQLite
                $Speciality->deleted_at = now();
                $Speciality->save();
            }
            
            $this->dispatch('notify', message: 'Especialidad desactivada correctamente.', type: 'error');
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