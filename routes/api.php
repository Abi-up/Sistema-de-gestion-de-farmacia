<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API - Sistema de Gestión de Farmacia
|--------------------------------------------------------------------------
*/

// Módulos de Catálogos Maestros (Fase 1)
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('presentaciones', PresentacionController::class);
Route::apiResource('marcas', MarcaController::class);
Route::apiResource('laboratorios', LaboratorioController::class);
Route::apiResource('proveedores', ProveedorController::class);
