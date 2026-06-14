<?php

namespace App\Livewire\System;

use App\Models\HsiRole;
use App\Models\DocumentType;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.app')]
class HsiRoles extends Component
{
    public ?int $editId = null;
    
    // Variables para el modal de eliminación
    public bool $confirmingDeletion = false;
    public ?int $roleIdToDelete = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    // Array para capturar los IDs de los documentos seleccionados en los checkboxes
    public array $selectedDocumentTypes = [];

    protected function rules(): array
    {
        return [
            // Ignoramos la validación unique para los registros que ya están en la papelera y para el mismo editId
            'name' => 'required|string|max:255|unique:hsi_roles,name,' . $this->editId . ',id,deleted_at,NULL',
            'selectedDocumentTypes' => 'array',
            'selectedDocumentTypes.*' => 'exists:document_types,id',
        ];
    }

    public function render()
    {
        return view('livewire.system.hsi-roles', [
            // Traemos roles activos e inactivos, e incluimos la relación de documentos para la tabla
            'hsiRoles' => HsiRole::withTrashed()->with('documentTypes')->orderBy('name', 'asc')->get(),
            // Para los checkboxes solo mostramos los documentos que están activos
            'availableDocumentTypes' => DocumentType::orderBy('name', 'asc')->get()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            $role = HsiRole::withTrashed()->where('id', $this->editId)->first();
            if ($role) {
                $role->name = mb_strtoupper($this->name);
                $role->save();
                
                // 1. Buscamos los IDs de los documentos que están vinculados a este rol pero están INACTIVOS (Soft Deleted)
                $inactiveLinkedDocs = $role->documentTypes()->onlyTrashed()->pluck('document_types.id')->toArray();
                
                // 2. Los unimos con los IDs que el usuario tildó en los checkboxes (los activos)
                $idsToSync = array_merge($this->selectedDocumentTypes, $inactiveLinkedDocs);
                
                // 3. Sincronizamos con la lista completa. Así, los inactivos sobreviven en la tabla pivote
                $role->documentTypes()->sync($idsToSync);
            }
            $this->dispatch('notify', message: 'Rol de HSI actualizado correctamente.', type: 'success');
        } else {
            $role = HsiRole::create(['name' => mb_strtoupper($this->name)]);
            
            // Al crear uno nuevo, solo adjuntamos lo que viene del form (no hay inactivos previos)
            $role->documentTypes()->attach($this->selectedDocumentTypes);
            
            $this->dispatch('notify', message: 'Rol de HSI creado y configurado correctamente.', type: 'success');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $role = HsiRole::withTrashed()->with('documentTypes')->find($id);
        
        if ($role) {
            $this->editId = $role->id;
            $this->name = $role->name;
            // Pluckeamos los IDs de los documentos relacionados para pre-tildar los checkboxes
            $this->selectedDocumentTypes = $role->documentTypes->pluck('id')->toArray();
        }
    }

    public function restore(int $id): void
    {
        HsiRole::onlyTrashed()->find($id)?->restore();
        $this->dispatch('notify', message: 'Rol reactivado.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->roleIdToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->roleIdToDelete = null;
    }

    public function executeDelete(): void
    {
        if ($this->roleIdToDelete) {
            HsiRole::where('id', $this->roleIdToDelete)->delete();
            $this->dispatch('notify', message: 'Rol de HSI eliminado (Inactivo).', type: 'error');
        }
        
        $this->cancelDelete();
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'editId', 'selectedDocumentTypes']);
        $this->resetValidation();
    }
}