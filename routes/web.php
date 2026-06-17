<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\System\Config;
use App\Livewire\System\DocumentTypes;
use App\Livewire\System\HsiRoles;
use App\Livewire\System\Occupations;
use App\Livewire\System\Specialties;
use App\Livewire\System\Users;
use App\Livewire\System\Services;
use App\Livewire\System\ActivityLogs;
use App\Livewire\Agents\AgentIndex;
use App\Livewire\Agents\AgentDashboard;
use App\Http\Controllers\Agents\AgentPrintController;
use App\Http\Controllers\Agents\AgentController;
use App\Livewire\HierarchicalUnits\Manager;

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::prefix('config')->middleware(['auth'])->group(function () {
    // Nota: A la ruta raíz de config y a los logs no les pasaste un permiso específico,
    // por lo que conservan solo la protección del login (auth).
    Route::get('/', Config::class)->name('system.config');
    Route::get('/activity-logs', ActivityLogs::class)->name('system.activity-logs');

    // Rutas con protección específica
    Route::get('/document-types', DocumentTypes::class)
        ->middleware('can:configurar.documentos')
        ->name('system.document-types');

    Route::get('/hsi-roles', HsiRoles::class)
        ->middleware('can:configurar.roles')
        ->name('system.hsi-roles');

    Route::get('/occupations', Occupations::class)
        ->middleware('can:configurar.profesiones')
        ->name('system.occupations');

    Route::get('/specialties', Specialties::class)
        ->middleware('can:configurar.especialidades')
        ->name('system.specialties');

    Route::get('/users', Users::class)
        ->middleware('can:configurar.usuarios')
        ->name('system.users');

    Route::get('/services', Services::class)
        ->middleware('can:configurar.servicios')
        ->name('system.services');
});

Route::prefix('padron')->middleware(['auth'])->group(function () {
    // Listado general
    Route::get('/', AgentIndex::class)->name('agents.index');
    
    // Detalle del legajo (pasando el ID del agente por la URL)
    Route::get('/{agent}', AgentDashboard::class)->name('agents.show');
});

Route::get('/uujj', Manager::class)
    ->name('hierarchical-units.manager');

Route::get('/imprimir', AgentPrintController::class)->name('agents.print');
Route::get('/padron/{agent}/ficha/imprimir', [AgentController::class, 'printFicha'])->name('agents.print_ficha');


// Route::prefix('admin/configuracion')->middleware(['auth', 'role:Administrador'])->group(function () {
//     Route::get('/', ConfigIndex::class)->name('admin.config.index');
//     Route::get('/profesiones', ProfessionsConfig::class)->name('admin.config.professions');
//     Route::get('/especialidades', SpecialtiesConfig::class)->name('admin.config.specialties');
//     Route::get('/institucion', InstitutionConfig::class)->name('admin.config.institution');
// });

require __DIR__.'/auth.php';
