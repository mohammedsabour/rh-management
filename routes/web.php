<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventaireParEmplacementController;
use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\InventaireComptableController;
use App\Http\Controllers\EmployeesAttestationController;
use App\Http\Controllers\PvCommissionController;
use App\Http\Controllers\PvFinalController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/inventaire/par-emplacement', InventaireParEmplacementController::class)
      ->name('inventaire.par-emplacement');
Route::middleware('auth')->get('/inventaire/etiquettes', [EtiquetteController::class, 'index'])
    ->name('inventaire.etiquettes');
Route::middleware('auth')->get('/inventaire/inventaire-comptable', InventaireComptableController::class)
    ->name('inventaire.comptable');
Route::middleware('auth')->get('/Employe/{user}/attestation', EmployeesAttestationController::class)
    ->name('employee.attestation.download');
Route::get('/recolement/commissions/{commission}/pv', PvCommissionController::class)
    ->name('recolement.pv-commission');
Route::get('/recolement/{inventaire}/pv-final', PvFinalController::class)
    ->name('recolement.pv-final');