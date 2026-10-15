<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\InventaireComptableController;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth')->get('/inventaire/etiquettes', [EtiquetteController::class, 'index'])
    ->name('inventaire.etiquettes');
Route::middleware('auth')->get('/inventaire/inventaire-comptable', InventaireComptableController::class)
    ->name('inventaire.comptable');