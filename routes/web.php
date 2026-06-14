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

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::prefix('config')->middleware(['auth'])->group(function () {
    Route::get('/', Config::class)->name('system.config');
    Route::get('/document-types', DocumentTypes::class)->name('system.document-types');
    Route::get('/hsi-roles', HsiRoles::class)->name('system.hsi-roles');
    Route::get('/occupations', Occupations::class)->name('system.occupations');
    Route::get('/specialties', Specialties::class)->name('system.specialties');
    Route::get('/users', Users::class)->name('system.users');
    Route::get('/services', Services::class)->name('system.services');
    Route::get('/activity-logs', ActivityLogs::class)->name('system.activity-logs');
});

Route::prefix('padron')->middleware(['auth'])->group(function () {
    // Listado general
    Route::get('/', AgentIndex::class)->name('agents.index');
    
    // Detalle del legajo (pasando el ID del agente por la URL)
    Route::get('/{agent}', AgentDashboard::class)->name('agents.show');
});

Route::get('/imprimir', AgentPrintController::class)->name('agents.print');
Route::get('/padron/{agent}/ficha/imprimir', [AgentController::class, 'printFicha'])->name('agents.print_ficha');


// Route::prefix('admin/configuracion')->middleware(['auth', 'role:Administrador'])->group(function () {
//     Route::get('/', ConfigIndex::class)->name('admin.config.index');
//     Route::get('/profesiones', ProfessionsConfig::class)->name('admin.config.professions');
//     Route::get('/especialidades', SpecialtiesConfig::class)->name('admin.config.specialties');
//     Route::get('/institucion', InstitutionConfig::class)->name('admin.config.institution');
// });

require __DIR__.'/auth.php';
