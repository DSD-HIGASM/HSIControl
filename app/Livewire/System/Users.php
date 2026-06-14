<?php

namespace App\Livewire\System;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.app')]
class Users extends Component
{
    public ?int $editId = null;
    
    public bool $confirmingDeletion = false;
    public ?int $userIdToDelete = null;

    public ?int $dni = null;
    public string $password = '';
    
    // Array para capturar los IDs de los permisos seleccionados
    public array $selectedPermissions = [];

    protected function rules(): array
    {
        return [
            'dni' => 'required|integer|unique:users,dni,' . $this->editId . ',id,deleted_at,NULL',
            'password' => $this->editId ? 'nullable|string|min:8' : 'required|string|min:8',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ];
    }

    public function render()
    {
        return view('livewire.system.users', [
            // Traemos los usuarios con su agente vinculado y sus permisos asignados
            'users' => User::with(['agent', 'permissions'])->withTrashed()->orderBy('dni', 'asc')->get(),
            // Traemos todos los permisos disponibles para armar los checkboxes
            'availablePermissions' => Permission::orderBy('name', 'asc')->get()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            $user = User::withTrashed()->find($this->editId);
            if ($user) {
                $user->dni = $this->dni;
                
                if (!empty($this->password)) {
                    $user->password = Hash::make($this->password);
                }
                
                $user->save();
                
                // Sincronizamos los permisos usando el método nativo de Spatie
                // Esto borra los desmarcados y agrega los nuevos automáticamente
                $user->syncPermissions($this->selectedPermissions);
            }
            $this->dispatch('notify', message: 'Credencial y permisos actualizados correctamente.', type: 'success');
        } else {
            $user = User::create([
                'dni' => $this->dni,
                'password' => Hash::make($this->password),
            ]);
            
            // Asignamos los permisos al nuevo usuario
            $user->syncPermissions($this->selectedPermissions);
            
            $this->dispatch('notify', message: 'Credencial creada y configurada correctamente.', type: 'success');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $user = User::withTrashed()->with('permissions')->find($id);
        
        if ($user) {
            $this->editId = $user->id;
            $this->dni = $user->dni;
            $this->password = ''; 
            
            // Pluckeamos los IDs de los permisos que ya tiene para pre-tildar los checkboxes
            $this->selectedPermissions = $user->permissions->pluck('id')->toArray();
        }
    }

    public function restore(int $id): void
    {
        User::onlyTrashed()->find($id)?->restore();
        $this->dispatch('notify', message: 'Credencial reactivada.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        if (auth()->id() === $id) {
            $this->dispatch('notify', message: 'No podés desactivar tu propio acceso por seguridad.', type: 'error');
            return;
        }

        $this->userIdToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->userIdToDelete = null;
    }

    public function executeDelete(): void
    {
        if ($this->userIdToDelete) {
            $user = User::find($this->userIdToDelete);
            
            if ($user) {
                $user->deleted_at = now();
                $user->save();
            }
            
            $this->dispatch('notify', message: 'Credencial desactivada correctamente.', type: 'error');
        }
        
        $this->cancelDelete();
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['dni', 'password', 'editId', 'selectedPermissions']);
        $this->resetValidation();
    }
}