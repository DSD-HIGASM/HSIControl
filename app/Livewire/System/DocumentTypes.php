<?php

namespace App\Livewire\System;

use App\Models\DocumentType;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.app')]
class DocumentTypes extends Component
{
    public ?int $editId = null;
    
    // Variables para el modal de eliminación
    public bool $confirmingDeletion = false;
    public ?int $documentIdToDelete = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    protected function rules(): array
    {
        return [
            // Agregamos ,id,deleted_at,NULL para que la validación unique 
            // ignore los registros que ya están en la papelera
            'name' => 'required|string|max:255|unique:document_types,name,' . $this->editId . ',id,deleted_at,NULL',
        ];
    }

    public function render()
    {
        return view('livewire.system.document-types', [
            // withTrashed() trae activos e inactivos
            'documentTypes' => DocumentType::withTrashed()->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            $document = DocumentType::withTrashed()->where('id', $this->editId)->first();
            if ($document) {
                $document->name = mb_strtoupper($this->name);
                $document->save();
            }
            // Disparamos el Toast
            $this->dispatch('notify', message: 'Tipo de documento actualizado correctamente.', type: 'success');
        } else {
            DocumentType::create(['name' => mb_strtoupper($this->name)]);
            // Disparamos el Toast
            $this->dispatch('notify', message: 'Tipo de documento creado correctamente.', type: 'success');
        }

        $this->resetForm();
    }

    // Cambiamos el parámetro a int para poder usar withTrashed() y evitar errores
    public function edit(int $id): void
    {
        $documentType = DocumentType::withTrashed()->find($id);
        
        if ($documentType) {
            $this->editId = $documentType->id;
            $this->name = $documentType->name;
        }
    }

    // Nuevo método para reactivar registros
    public function restore(int $id): void
    {
        DocumentType::onlyTrashed()->find($id)?->restore();
        // Disparamos el Toast
        $this->dispatch('notify', message: 'Tipo de documento reactivado.', type: 'success');
    }

    // Dispara el modal
    public function confirmDelete(int $id): void
    {
        $this->documentIdToDelete = $id;
        $this->confirmingDeletion = true;
    }

    // Cancela la eliminación y cierra el modal
    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->documentIdToDelete = null;
    }

    // Ejecuta la eliminación real
    public function executeDelete(): void
    {
        if ($this->documentIdToDelete) {
            $doc = DocumentType::find($this->documentIdToDelete);
            
            if ($doc) {
                $doc->deleted_at = now();
                $doc->save();
            }
            
            $this->dispatch('notify', message: 'Tipo de documento eliminado (Inactivo).', type: 'error');
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