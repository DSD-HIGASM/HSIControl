<?php

namespace App\Livewire\HierarchicalUnits;

use App\Models\HierarchicalUnit;
use App\Models\HierarchicalUnitType;
use App\Models\Speciality;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Manager extends Component
{
    use WithFileUploads;
    public $unit_id = null;

    public $alias = '';

    public $type_id = '';

    public $hierarchical_unit_id_to_report = null;

    public $clinical_specialty_id = null;

    public $parent_ids = [];

    public $search_parents = '';

    public $is_editing = false;

    public $showPanel = false;

    public bool $showImportModal = false;

    public $csv_file = null;

    public bool $clean_tables = false;

    public string $importStep = 'upload'; // 'upload' | 'mapping'

    public array $unresolvedSpecialties = [];

    public array $resolvedSpecialties = [];

    public array $parsedRows = [];

    public function openImportModal()
    {
        $this->reset(['csv_file', 'clean_tables', 'importStep', 'unresolvedSpecialties', 'resolvedSpecialties', 'parsedRows']);
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function analyzeCsv()
    {
        $this->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Debes seleccionar un archivo CSV.',
            'csv_file.mimes' => 'El formato debe ser CSV o TXT.',
            'csv_file.max' => 'El archivo no puede superar 10 MB.',
        ]);

        $path = $this->csv_file->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 2000, ',');
        $colIndex = array_flip($header);

        $this->parsedRows = [];
        $unitsNeedingSpecialty = [];

        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            $id = $row[$colIndex['id']] ?? null;
            $alias = $row[$colIndex['alias']] ?? null;
            $typeId = isset($row[$colIndex['typeId']]) ? (int) $row[$colIndex['typeId']] : null;
            $idToReport = ! empty($row[$colIndex['hierarchicalUnitIdToReport']]) ? (int) $row[$colIndex['hierarchicalUnitIdToReport']] : null;
            $closestId = ! empty($row[$colIndex['closestServiceId']]) ? (int) $row[$colIndex['closestServiceId']] : null;
            $hasSpecialtyMark = ! empty($row[$colIndex['clinicalSpecialtyId']]) || $typeId === 8;
            $deleted = strtolower($row[$colIndex['deleted']] ?? 'false') === 'true';

            if (! $id || ! $alias || $deleted) {
                continue;
            }

            $this->parsedRows[] = [
                'id' => (int) $id,
                'alias' => trim($alias),
                'type_id' => $typeId,
                'parent_id' => $idToReport,
                'closest_id' => $closestId,
                'needs_specialty' => $hasSpecialtyMark,
            ];

            if ($hasSpecialtyMark) {
                $unitsNeedingSpecialty[] = [
                    'id' => (int) $id,
                    'alias' => trim($alias),
                ];
            }
        }
        fclose($handle);

        // Algoritmo de similitud
        $specialties = Speciality::all();
        $this->resolvedSpecialties = [];
        $this->unresolvedSpecialties = [];

        foreach ($unitsNeedingSpecialty as $unit) {
            $cleanedAlias = $this->cleanAlias($unit['alias']);
            $match = $this->findBestSpecialtyMatch($cleanedAlias, $specialties);

            if ($match && $match['similarity'] >= 80.0) {
                // Coincidencia con alta certeza
                $this->resolvedSpecialties[$unit['id']] = $match['specialty_id'];
            } else {
                // Certeza baja o ambigüedad: preguntar al usuario
                $this->unresolvedSpecialties[$unit['id']] = [
                    'unit_id' => $unit['id'],
                    'alias' => $unit['alias'],
                    'suggested_id' => $match ? $match['specialty_id'] : null,
                    'suggested_name' => $match ? $match['name'] : 'Sin coincidencia clara',
                    'similarity' => $match ? round($match['similarity'], 1) : 0,
                    'selected_id' => ($match && $match['similarity'] >= 50.0) ? $match['specialty_id'] : '',
                ];
            }
        }

        if (count($this->unresolvedSpecialties) > 0) {
            $this->importStep = 'mapping';
        } else {
            $this->executeImport();
        }
    }

    public function executeImport()
    {
        // Consolidamos las asignaciones automáticas con las manuales elegidas en el modal
        $finalSpecialties = $this->resolvedSpecialties;
        foreach ($this->unresolvedSpecialties as $unitId => $item) {
            $finalSpecialties[$unitId] = ! empty($item['selected_id']) ? (int) $item['selected_id'] : null;
        }

        DB::beginTransaction();

        try {
            Schema::disableForeignKeyConstraints();

            if ($this->clean_tables) {
                DB::table('hierarchical_unit_relations')->truncate();
                DB::table('agent_hierarchical_unit')->truncate();
                DB::table('hierarchical_units')->truncate();
            }

            // Asegurar que los tipos existan desde el Enum
            foreach (\App\Enums\HierarchicalUnitType::cases() as $case) {
                DB::table('hierarchical_unit_types')->updateOrInsert(
                    ['id' => $case->value],
                    ['description' => $case->label(), 'updated_at' => now(), 'created_at' => now()]
                );
            }

            // FASE 1: Inserción base
            foreach ($this->parsedRows as $unit) {
                $exists = DB::table('hierarchical_units')->where('id', $unit['id'])->exists();

                DB::table('hierarchical_units')->updateOrInsert(
                    ['id' => $unit['id']],
                    [
                        'alias' => $unit['alias'],
                        'type_id' => $unit['type_id'],
                        'institution_id' => 484,
                        'created_by' => null,
                        'updated_by' => null,
                        'updated_at' => now(),
                        'created_at' => $exists ? DB::raw('created_at') : now(),
                    ]
                );
            }

            // FASE 2: Dependencias y Especialidades emparejadas
            foreach ($this->parsedRows as $unit) {
                $specialtyId = $finalSpecialties[$unit['id']] ?? null;

                DB::table('hierarchical_units')->where('id', $unit['id'])->update([
                    'hierarchical_unit_id_to_report' => $unit['parent_id'],
                    'closest_service_id' => $unit['closest_id'],
                    'clinical_specialty_id' => $specialtyId,
                ]);

                if ($unit['parent_id']) {
                    DB::table('hierarchical_unit_relations')->updateOrInsert(
                        [
                            'hierarchical_unit_parent_id' => $unit['parent_id'],
                            'hierarchical_unit_child_id' => $unit['id'],
                        ],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }

            Schema::enableForeignKeyConstraints();
            DB::commit();

            $this->showImportModal = false;
        $this->importStep = 'upload';
        $this->reset(['csv_file', 'clean_tables', 'unresolvedSpecialties', 'resolvedSpecialties', 'parsedRows']);

        session()->flash('status', 'Unidades jerárquicas importadas y especialidades asociadas exitosamente.');

            $allUnits = HierarchicalUnit::with(['type', 'parents', 'children', 'specialty'])->orderBy('alias')->get();
            $this->dispatch('relations-updated',
                map: $this->cloneRelationsMap(),
                unitsData: $allUnits->keyBy('id')->map(fn ($u) => ['alias' => strtolower($u->alias)])->toArray()
            );
            $this->dispatch('network-updated', data: $this->getNetworkData());

        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            DB::rollBack();
            $this->addError('csv_file', 'Error al importar los datos: ' . $e->getMessage());
        }
    }

    private function cleanAlias(string $alias): string
    {
        $text = preg_replace('/^(Servicio de|Departamento de|Comit[eé] de|Unidad de|Sala de)\s+/iu', '', $alias);
        $text = preg_replace('/\s*\(\d+\)$/u', '', $text);
        $text = mb_strtolower(trim($text), 'UTF-8');
        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ä', 'ë', 'ï', 'ö', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
            $text
        );
    }

    private function findBestSpecialtyMatch(string $cleanedAlias, $specialties): ?array
    {
        $bestMatch = null;
        $highestScore = 0.0;

        foreach ($specialties as $specialty) {
            $cleanedSpec = $this->cleanAlias($specialty->name);

            if ($cleanedAlias === $cleanedSpec) {
                return ['specialty_id' => $specialty->id, 'name' => $specialty->name, 'similarity' => 100.0];
            }

            similar_text($cleanedAlias, $cleanedSpec, $percent);

            // Bonificación por inclusión de subcadena (ej: "toxicologia" en "toxicologia clinica")
            if (str_contains($cleanedSpec, $cleanedAlias) || str_contains($cleanedAlias, $cleanedSpec)) {
                $percent = max($percent, 85.0);
            }

            if ($percent > $highestScore) {
                $highestScore = $percent;
                $bestMatch = ['specialty_id' => $specialty->id, 'name' => $specialty->name, 'similarity' => $percent];
            }
        }

        return $bestMatch;
    }

    public function importCsv()
    {
        $this->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Debes seleccionar un archivo CSV.',
            'csv_file.mimes' => 'El archivo debe ser de formato CSV o TXT.',
            'csv_file.max' => 'El archivo no puede superar los 10 MB.',
        ]);

        // Opción de limpieza previa
        if ($this->clean_tables) {
            Schema::disableForeignKeyConstraints();
            DB::table('hierarchical_unit_relations')->truncate();
            DB::table('agent_hierarchical_unit')->truncate();
            DB::table('hierarchical_units')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        // Asegurar que los tipos del Enum existan en la base de datos
        foreach (\App\Enums\HierarchicalUnitType::cases() as $case) {
            DB::table('hierarchical_unit_types')->updateOrInsert(
                ['id' => $case->value],
                [
                    'description' => $case->label(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $path = $this->csv_file->getRealPath();
        $existingSpecialties = DB::table('specialities')->pluck('id')->flip()->toArray();

        DB::beginTransaction();

        try {
            // Fase 1: Creación/actualización base
            $handle = fopen($path, 'r');
            $header = fgetcsv($handle, 2000, ',');
            $colIndex = array_flip($header);

            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                $id = $row[$colIndex['id']] ?? null;
                $alias = $row[$colIndex['alias']] ?? null;
                $typeId = isset($row[$colIndex['typeId']]) ? (int) $row[$colIndex['typeId']] : null;
                $deleted = strtolower($row[$colIndex['deleted']] ?? 'false') === 'true';

                if (! $id || ! $alias || $deleted) {
                    continue;
                }

                $exists = DB::table('hierarchical_units')->where('id', $id)->exists();

                DB::table('hierarchical_units')->updateOrInsert(
                    ['id' => $id],
                    [
                        'alias' => trim($alias),
                        'type_id' => $typeId,
                        'institution_id' => 484,
                        'created_by' => null,
                        'updated_by' => null,
                        'updated_at' => now(),
                        'created_at' => $exists ? DB::raw('created_at') : now(),
                    ]
                );
            }
            fclose($handle);

            // Fase 2: Vinculación de dependencias y tabla pivot
            $handle = fopen($path, 'r');
            fgetcsv($handle, 2000, ','); // Saltar cabecera

            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                $id = $row[$colIndex['id']] ?? null;
                $idToReport = ! empty($row[$colIndex['hierarchicalUnitIdToReport']]) ? (int) $row[$colIndex['hierarchicalUnitIdToReport']] : null;
                $closestId = ! empty($row[$colIndex['closestServiceId']]) ? (int) $row[$colIndex['closestServiceId']] : null;
                $specialtyId = ! empty($row[$colIndex['clinicalSpecialtyId']]) ? (int) $row[$colIndex['clinicalSpecialtyId']] : null;
                $deleted = strtolower($row[$colIndex['deleted']] ?? 'false') === 'true';

                if (! $id || $deleted) {
                    continue;
                }

                if ($specialtyId && ! isset($existingSpecialties[$specialtyId])) {
                    $specialtyId = null;
                }

                DB::table('hierarchical_units')->where('id', $id)->update([
                    'hierarchical_unit_id_to_report' => $idToReport,
                    'closest_service_id' => $closestId,
                    'clinical_specialty_id' => $specialtyId,
                ]);

                if ($idToReport) {
                    DB::table('hierarchical_unit_relations')->updateOrInsert(
                        [
                            'hierarchical_unit_parent_id' => $idToReport,
                            'hierarchical_unit_child_id' => $id,
                        ],
                        [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
            fclose($handle);

            DB::commit();

            $this->reset(['csv_file', 'clean_tables', 'showImportModal']);
            session()->flash('status', 'Unidades jerárquicas importadas y vinculadas con éxito.');

            // Sincronizar el tablero y el grafo de Vis.js
            $allUnits = HierarchicalUnit::with(['type', 'parents', 'children', 'specialty'])->orderBy('alias')->get();
            $this->dispatch('relations-updated',
                map: $this->cloneRelationsMap(),
                unitsData: $allUnits->keyBy('id')->map(fn ($u) => ['alias' => strtolower($u->alias)])->toArray()
            );
            $this->dispatch('network-updated', data: $this->getNetworkData());

        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            $this->addError('csv_file', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    private function cloneRelationsMap()
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
            if (! isset($grouped[$lvl])) {
                $grouped[$lvl] = [];
            }
            $grouped[$lvl][] = $unit;
        }

        ksort($grouped);

        return $grouped;
    }

    private function getNetworkData()
    {
        $allUnits = HierarchicalUnit::with(['type', 'parents'])->get();
        $nodes = [];
        $edges = [];

        foreach ($allUnits as $unit) {
            $tipo = $unit->type->description ?? 'Sin Tipo';

            // Dividimos el alias envuelto y le aplicamos <b> a cada renglón individualmente
            $lineas = explode("\n", wordwrap($unit->alias, 25, "\n"));
            $nombreBold = implode("\n", array_map(fn ($l) => '<b>'.trim($l).'</b>', $lineas));

            // Nombre con formato por línea arriba, tipo en cursiva abajo
            $label = $nombreBold."\n<i>".strtoupper($tipo).'</i>';

            $nodes[] = [
                'id' => $unit->id,
                'label' => $label,
                'title' => 'Opciones de la unidad',
            ];

            foreach ($unit->parents as $parent) {
                $edges[] = [
                    'from' => $parent->id,
                    'to' => $unit->id,
                ];
            }
        }

        return ['nodes' => $nodes, 'edges' => $edges];
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

        $allUnits = HierarchicalUnit::with(['type', 'parents', 'children', 'specialty'])->orderBy('alias')->get();

        $typesOptions = $types->map(function ($t) {
            return ['id' => $t->id, 'name' => $t->description];
        })->toArray();

        $serviceUnitsOptions = $serviceUnits->map(function ($u) {
            return ['id' => $u->id, 'name' => $u->alias];
        })->toArray();

        return view('livewire.hierarchical-units.manager', [
            'typesOptions' => $typesOptions,
            'serviceUnitsOptions' => $serviceUnitsOptions,
            'specialties' => Speciality::orderBy('name')->get(),
            'formSearchUnits' => HierarchicalUnit::when($this->search_parents, function ($q) {
                $q->where('alias', 'like', '%'.$this->search_parents.'%');
            })->orderBy('alias')->get(),
            'isServicioSelected' => $isServicioSelected,

            'groupedUnits' => $this->getGroupedByLevel($allUnits),
            'relationsMap' => $this->cloneRelationsMap(),
            'unitsData' => $allUnits->keyBy('id')->map(fn ($u) => ['alias' => strtolower($u->alias)])->toArray(),

            'networkData' => $this->getNetworkData(),
        ]);
    }

    public function createChild($parentId)
    {
        $this->resetForm();
        $this->parent_ids = [(string) $parentId];
        $this->showPanel = true;
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
            $this->parent_ids = $unit->parents->pluck('id')->map(fn ($id) => (string) $id)->toArray();

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
        $rules = [
            'alias' => ['required', 'string', 'max:255'],
            'type_id' => ['required', 'exists:hierarchical_unit_types,id'],
            'hierarchical_unit_id_to_report' => ['nullable', 'exists:hierarchical_units,id'],
            'parent_ids' => ['array'],
            'parent_ids.*' => ['exists:hierarchical_units,id'],
        ];

        $types = HierarchicalUnitType::all();
        $type = $types->firstWhere('id', $this->type_id);

        if ($type && stripos($type->description, 'servicio') !== false) {
            $rules['clinical_specialty_id'] = ['nullable', 'exists:specialities,id'];
        }

        $validated = $this->validate($rules, [
            'alias.required' => 'El nombre de la unidad es obligatorio.',
            'type_id.required' => 'Debes seleccionar una categoría.',
        ]);

        if ($type && stripos($type->description, 'servicio') !== false) {
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

        $allUnits = HierarchicalUnit::with(['type', 'parents', 'children', 'specialty'])->orderBy('alias')->get();

        // Despachamos datos sincronizados para que Alpine JS no se rompa al crear/editar
        $this->dispatch('relations-updated',
            map: $this->cloneRelationsMap(),
            unitsData: $allUnits->keyBy('id')->map(fn ($u) => ['alias' => strtolower($u->alias)])->toArray()
        );
        $this->dispatch('network-updated', data: $this->getNetworkData());
    }

    public function delete()
    {
        if ($this->unit_id) {
            HierarchicalUnit::findOrFail($this->unit_id)->delete();
            session()->flash('status', 'Unidad eliminada correctamente.');
            $this->closePanel();

            $allUnits = HierarchicalUnit::with(['type', 'parents', 'children', 'specialty'])->orderBy('alias')->get();

            $this->dispatch('relations-updated',
                map: $this->cloneRelationsMap(),
                unitsData: $allUnits->keyBy('id')->map(fn ($u) => ['alias' => strtolower($u->alias)])->toArray()
            );
            $this->dispatch('network-updated', data: $this->getNetworkData());
        }
    }

    private function resetForm()
    {
        $this->reset([
            'unit_id', 'alias', 'type_id', 'hierarchical_unit_id_to_report',
            'clinical_specialty_id', 'parent_ids',
            'search_parents', 'is_editing',
        ]);
        $this->resetValidation();
    }
}
