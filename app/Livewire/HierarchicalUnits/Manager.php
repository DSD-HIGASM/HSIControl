<?php

namespace App\Livewire\HierarchicalUnits;

use App\Models\HierarchicalUnit;
use App\Models\HierarchicalUnitType;
use App\Models\Speciality;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Manager extends Component
{
    public $unit_id = null;
    public $alias = '';
    public $type_id = '';
    public $hierarchical_unit_id_to_report = null;
    public $clinical_specialty_id = null;
    public $parent_ids = [];

    public $search_parents = ''; 
    public $is_editing = false;
    public $showPanel = false;

    // Función privada para armar el mapa. Ya no es una variable pública.
    private function getRelationsMap()
    {
        $allUnits = HierarchicalUnit::with(['parents', 'children'])->get();
        $map = [];
        foreach ($allUnits as $unit) {
            $map[$unit->id] = [
                'parents' => $unit->parents->pluck('id')->toArray(),
                'children' => $unit->children->pluck('id')->toArray(),
            ];
        }
        return $map;
    }

    private function getGroupedByLevel($allUnits)
    {
        $depths = [];
        
        foreach ($allUnits as $unit) {
            $depths[$unit->id] = $unit->parents->isEmpty() ? 1 : 0;
        }

        $changed = true;
        $limit = 0;
        while ($changed && $limit < 30) {
            $changed = false;
            foreach ($allUnits as $unit) {
                if ($unit->parents->isNotEmpty()) {
                    $maxParentDepth = 0;
                    $allParentsResolved = true;
                    
                    foreach ($unit->parents as $parent) {
                        $pDepth = $depths[$parent->id] ?? 0;
                        if ($pDepth === 0) {
                            $allParentsResolved = false;
                        }
                        if ($pDepth > $maxParentDepth) {
                            $maxParentDepth = $pDepth;
                        }
                    }

                    if ($allParentsResolved && $maxParentDepth > 0) {
                        $newDepth = $maxParentDepth + 1;
                        if ($depths[$unit->id] !== $newDepth) {
                            $depths[$unit->id] = $newDepth;
                            $changed = true;
                        }
                    }
                }
            }
            $limit++;
        }

        $grouped = [];
        foreach ($allUnits as $unit) {
            $lvl = $depths[$unit->id] ?? 99; 
            if (!isset($grouped[$lvl])) {
                $grouped[$lvl] = [];
            }
            $grouped[$lvl][] = $unit;
        }
        
        ksort($grouped);
        return $grouped;
    }

    public function render()
    {
        $types = HierarchicalUnitType::orderBy('description')->get();

        $isServicioSelected = false;
        if ($this->type_id) {
            $type = $types->firstWhere('id', $this->type_id);
            if ($type && stripos($type->description, 'servicio') !== false) {
                $isServicioSelected = true;
            }
        }

        $serviceUnits = HierarchicalUnit::whereHas('type', function ($q) {
            $q->where('description', 'like', '%servicio%');
        })->orderBy('alias')->get();

        $allUnits = HierarchicalUnit::with(['type', 'parents', 'children'])->orderBy('alias')->get();
        
        $groupedUnits = $this->getGroupedByLevel($allUnits);

        return view('livewire.hierarchical-units.manager', [
            'types' => $types,
            'specialties' => Speciality::orderBy('name')->get(),
            'formSearchUnits' => HierarchicalUnit::when($this->search_parents, function($q) {
                                $q->where('alias', 'like', '%' . $this->search_parents . '%');
                            })->orderBy('alias')->get(),
            'serviceUnits' => $serviceUnits,
            'isServicioSelected' => $isServicioSelected,
            'groupedUnits' => $groupedUnits,
            // Pasamos el mapa directamente a la vista
            'relationsMap' => $this->getRelationsMap(),
        ]);
    }

    public function openPanel($id = null)
    {
        $this->resetForm();

        if ($id) {
            $unit = HierarchicalUnit::with('parents')->findOrFail($id);
            $this->unit_id = $unit->id;
            $this->alias = $unit->alias;
            $this->type_id = $unit->type_id;
            $this->hierarchical_unit_id_to_report = $unit->hierarchical_unit_id_to_report;
            $this->clinical_specialty_id = $unit->clinical_specialty_id;
            $this->parent_ids = $unit->parents->pluck('id')->toArray();
            
            $this->is_editing = true;
        }

        $this->showPanel = true;
    }

    public function closePanel()
    {
        $this->showPanel = false;
        $this->resetForm();
    }

    public function save()
    {
        $validated = $this->validate([
            'alias' => ['required', 'string', 'max:255'],
            'type_id' => ['required', 'exists:hierarchical_unit_types,id'],
            'hierarchical_unit_id_to_report' => ['nullable', 'exists:hierarchical_units,id'],
            'parent_ids' => ['array'],
            'parent_ids.*' => ['exists:hierarchical_units,id'],
        ], [
            'alias.required' => 'El nombre de la unidad es obligatorio.',
            'type_id.required' => 'Debes seleccionar una categoría.',
        ]);

        $types = HierarchicalUnitType::all();
        $type = $types->firstWhere('id', $this->type_id);
        
        if ($type && stripos($type->description, 'servicio') !== false) {
            $this->validate(['clinical_specialty_id' => ['nullable', 'exists:specialities,id']]);
            $validated['clinical_specialty_id'] = $this->clinical_specialty_id;
        } else {
            $validated['clinical_specialty_id'] = null; 
        }

        if ($this->is_editing && in_array($this->unit_id, $this->parent_ids)) {
            $this->addError('parent_ids', 'Una unidad no puede depender de sí misma.');
            return;
        }

        $validated['updated_by'] = auth()->id();

        if ($this->is_editing) {
            $unit = HierarchicalUnit::findOrFail($this->unit_id);
            $unit->update($validated);
        } else {
            $validated['institution_id'] = 484;
            $validated['created_by'] = auth()->id();
            $unit = HierarchicalUnit::create($validated);
        }

        $unit->parents()->sync($this->parent_ids);

        session()->flash('status', $this->is_editing ? 'Unidad actualizada correctamente.' : 'Unidad creada con éxito.');
        $this->closePanel();
        
        // Magia: Enviamos un evento con el mapa fresco para que JS lo atrape silenciosamente
        $this->dispatch('relations-updated', map: $this->getRelationsMap());
    }

    public function delete()
    {
        if ($this->unit_id) {
            HierarchicalUnit::findOrFail($this->unit_id)->delete();
            session()->flash('status', 'Unidad eliminada correctamente.');
            $this->closePanel();
            
            // Magia: Enviamos un evento con el mapa fresco para que JS lo atrape silenciosamente
            $this->dispatch('relations-updated', map: $this->getRelationsMap());
        }
    }

    private function resetForm()
    {
        $this->reset([
            'unit_id', 'alias', 'type_id', 'hierarchical_unit_id_to_report', 
            'clinical_specialty_id', 'parent_ids', 
            'search_parents', 'is_editing'
        ]);
        $this->resetValidation();
    }
}