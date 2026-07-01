<?php
use Illuminate\Http\Request;
use App\Models\PreRegistration;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HsiSyncController;

Route::middleware('auth:sanctum')->post('/pre-register', function (Request $request) {
    $preReg = PreRegistration::create([
        'data'      => $request->json()->all(),
        'user_id'   => $request->user()->id,
        'is_global' => $request->boolean('is_global', false),
    ]);

    return response()->json(['token' => $preReg->id]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/hsi-sync', [HsiSyncController::class, 'store'])->name('api.hsi_sync.store');
});