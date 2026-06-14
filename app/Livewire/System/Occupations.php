<?php

namespace App\Livewire\System;

use App\Models\Occupation;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.app')]
class Occupations extends Component
{
    public ?int $editId = null;
    
    public bool $confirmingDeletion = false;
    public ?int $occupationIdToDelete = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    protected function rules(): array
    {
        return [
            // Ignoramos unique para el mismo ID y para los que están en la papelera
            'name' => 'required|string|max:255|unique:occupations,name,' . $this->editId . ',id,deleted_at,NULL',
        ];
    }

    public function render()
    {
        return view('livewire.system.occupations', [
            'occupations' => Occupation::withTrashed()->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            $occupation = Occupation::withTrashed()->where('id', $this->editId)->first();
            if ($occupation) {
                $occupation->name = mb_strtoupper($this->name);
                $occupation->save();
            }
            $this->dispatch('notify', message: 'Ocupación actualizada correctamente.', type: 'success');
        } else {
            Occupation::create(['name' => mb_strtoupper($this->name)]);
            $this->dispatch('notify', message: 'Ocupación creada correctamente.', type: 'success');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $occupation = Occupation::withTrashed()->find($id);
        
        if ($occupation) {
            $this->editId = $occupation->id;
            $this->name = $occupation->name;
        }
    }

    public function restore(int $id): void
    {
        Occupation::onlyTrashed()->find($id)?->restore();
        $this->dispatch('notify', message: 'Ocupación reactivada.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->occupationIdToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->occupationIdToDelete = null;
    }

    public function executeDelete(): void
    {
        if ($this->occupationIdToDelete) {
            $occupation = Occupation::find($this->occupationIdToDelete);
            
            if ($occupation) {
                // Borrado lógico seguro para SQLite
                $occupation->deleted_at = now();
                $occupation->save();
            }
            
            $this->dispatch('notify', message: 'Ocupación desactivada correctamente.', type: 'error');
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