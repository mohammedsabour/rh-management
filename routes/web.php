<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\InventaireComptableController;
use App\Http\Controllers\EmployeesAttestationController;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth')->get('/inventaire/etiquettes', [EtiquetteController::class, 'index'])
    ->name('inventaire.etiquettes');
Route::middleware('auth')->get('/inventaire/inventaire-comptable', InventaireComptableController::class)
    ->name('inventaire.comptable');
Route::middleware('auth')->get('/Employe/{user}/attestation', EmployeesAttestationController::class)
    ->name('employee.attestation.download');